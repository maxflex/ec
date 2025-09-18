<script lang="ts" setup>
import type { StatsDialog } from '#build/components'
import type { StatsApiResponse, StatsDisplay, StatsListResource, StatsParams } from '~/components/Stats'
import type { StatsMetric } from '~/components/Stats/Metrics'
import { mdiDownload, mdiTune } from '@mdi/js'
import { cloneDeep } from 'lodash-es'
import { defaultStatsParams, formatDateMode, StatsDisplayIcon, StatsDisplayLabel } from '~/components/Stats'
import { MetricComponents } from '~/components/Stats/Metrics'

const statsDialog = ref<InstanceType<typeof StatsDialog>>()

const params = ref<StatsParams>(cloneDeep(defaultStatsParams))
const display = ref<StatsDisplay>('table')

// сохраняем параметры ответа сервера, чтобы не зависеть от текущих параметров
// (например, если снесли метрику или изменили режим на "по годам", чтобы не менялось форматирование)
const responseParams = ref<StatsParams>()

const loading = ref(false)
const exporting = ref(false)
const isLastPage = ref(false)

const items = ref<StatsListResource[]>([])
const totals = ref<number[]>([])
const page = ref<number>(0)
const refreshKey = ref<number>(0)
const itemsReversed = computed(() => items.value.slice().reverse())

let scrollContainer: HTMLElement | null = null

function onGo(p: StatsParams) {
  params.value = cloneDeep(p)
  isLastPage.value = false
  loadData()
}

async function loadMore() {
  if (loading.value) {
    return
  }
  loading.value = true
  page.value++
  const { data } = await useHttp<StatsApiResponse>(
    `stats`,
    {
      method: 'post',
      body: {
        ...params.value,
        page: page.value,
      },
    },
  )
  if (data.value) {
    if (page.value === 1) {
      responseParams.value = cloneDeep(params.value)
      items.value = data.value.data
      totals.value = data.value.totals
    }
    else {
      items.value = items.value.concat(data.value.data)
    }
    isLastPage.value = data.value.is_last_page
  }
  refreshKey.value++
  loading.value = false
}

async function exportDownload() {
  exporting.value = true
  const { data } = await useHttp<Blob>(
    `stats`,
    {
      method: 'post',
      body: {
        ...params.value,
        export: true,
      },
      responseType: 'blob', // Important: Treat the response as a binary Blob
    },
  )

  // Create a Blob from the response
  const blob = new Blob([data.value!])

  // Create a URL for the Blob
  const url = window.URL.createObjectURL(blob)

  // Create a temporary link and trigger the download
  const link = document.createElement('a')
  link.href = url
  link.download = 'stats.xlsx' // Specify the file name
  document.body.appendChild(link)
  link.click()

  // Clean up
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
  exporting.value = false
}

function loadData() {
  page.value = 0
  display.value = 'table'
  if (scrollContainer) {
    scrollContainer.scrollTop = 0
  }
  loadMore()
}

function getWidth(m: StatsMetric) {
  const { width } = MetricComponents[m.metric]
  return {
    width: `${width || 120}px`,
  }
}

function onScroll() {
  if (!scrollContainer || loading.value || isLastPage.value || !items.value.length) {
    return
  }
  const { scrollTop, scrollHeight, clientHeight } = scrollContainer
  const scrollPosition = scrollTop + clientHeight
  const scrollThreshold = scrollHeight * 0.9

  if (scrollPosition >= scrollThreshold) {
    loadMore()
  }
}

function toggleChart() {
  const displayOptions = Object.keys(StatsDisplayLabel)
  const index = displayOptions.findIndex(e => e === display.value)
  const nextIndex = index + 1 === displayOptions.length ? 0 : index + 1
  display.value = displayOptions[nextIndex] as StatsDisplay
}

onMounted(() => {
  scrollContainer = document.documentElement.querySelector('main')
  scrollContainer?.addEventListener('scroll', onScroll)
})

onUnmounted(() => {
  scrollContainer?.removeEventListener('scroll', onScroll)
})

nextTick(() => statsDialog.value?.open())
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="loading" />
  </v-fade-transition>
  <div class="table table-stats" :class="`table-stats--${responseParams?.mode} table-stats--${display}`">
    <div class="table-stats__header">
      <div class="table-stats__header-mode">
        <v-btn
          :icon="mdiTune"
          :size="38"
          variant="text" @click="statsDialog?.open(responseParams !== undefined)"
        />
        <v-btn
          :icon="mdiDownload"
          :size="38"
          :disabled="!(responseParams && responseParams.metrics.length)"
          :loading="exporting"
          variant="text"
          @click="exportDownload()"
        />
        <v-btn
          :icon="StatsDisplayIcon[display]"
          :disabled="!(responseParams?.mode === 'month')"
          :size="38"
          variant="text"
          @click="toggleChart()"
        />
      </div>
      <template v-if="responseParams">
        <template v-for="metric in responseParams.metrics">
          <div
            v-if="!metric.hidden"
            :key="metric.id"
            :style="getWidth(metric)"
            class="table-stats__header-metric"
          >
            <span>
              {{ metric.label }}
            </span>
          </div>
        </template>
      </template>
      <!-- визуальный фикс, когда нет данных -->
      <div v-else></div>
    </div>
    <template v-if="responseParams">
      <template v-if="display === 'table'">
        <div v-for="{ date, values } in items" :key="date" class="table-stats__body">
          <div class="table-stats__date">
            {{ formatDateMode(date, responseParams.mode, responseParams.date_to) }}
          </div>
          <template v-for="(value, index) in values">
            <div
              v-if="!responseParams.metrics[index].hidden"
              :key="index"
              :class="`text-${responseParams.metrics[index].color}`"
              :style="getWidth(responseParams.metrics[index])"
            >
              {{ value ? formatPrice(value) : '' }}
            </div>
          </template>
        </div>
        <div class="table-stats__footer table-stats__body">
          <div class="table-stats__date">
            итого
          </div>
          <template v-for="(total, index) in totals">
            <div
              v-if="!responseParams.metrics[index].hidden"
              :key="index"
              :style="getWidth(responseParams.metrics[index])"
              :class="`text-${responseParams.metrics[index].color}`"
            >
              <span>
                {{ total ? formatPrice(total) : '' }}
              </span>
            </div>
          </template>
        </div>
      </template>
      <StatsChartBar v-if="display === 'bar'" :key="refreshKey" :items="itemsReversed" :params="responseParams" />
      <StatsChartYears v-if="display === 'years'" :key="refreshKey" :items="itemsReversed" :params="responseParams" />
    </template>
  </div>
  <StatsDialog ref="statsDialog" @go="onGo" />
</template>

<style lang="scss">
.table-stats {
  $padding: 0 20px;
  width: max-content;
  min-width: 100%;

  &:not(.table-stats--table) {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    & > div {
      &:last-child:not(:first-child) {
        flex: 1;
        canvas {
          padding: 50px 50px 50px 20px;
        }
      }
    }
  }

  & > div {
    gap: 0 !important;
    padding: 0 !important;
    & > div {
      &:first-child {
        width: 150px;
      }
    }
  }
  &--week {
    & > div > div:first-child {
      width: 200px !important;
    }
  }
  &__header {
    position: sticky !important;
    top: 0;
    text-transform: lowercase;
    background: rgb(var(--v-theme-bg));
    z-index: 1;
    &-mode {
      padding: 0 !important;
      height: 57px;
      border-right: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
      padding: $padding;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      position: sticky;
      left: 0;
      background: rgb(var(--v-theme-bg));
      z-index: 1;
      gap: 4px;
    }
    &-metric {
      position: relative;
      line-height: 20px;
      padding: $padding;
    }
    &-metric {
      align-self: stretch;
      display: flex;
      align-items: center;
      user-select: none;
    }
    &-metrics {
      display: flex;
      align-self: stretch;
    }
    &-actions {
      padding: $padding;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      justify-content: flex-end;
    }
  }
  &__footer {
    position: sticky !important;
    bottom: 0;
    text-transform: lowercase;
    background: rgb(var(--v-theme-bg));
    z-index: 1;
    border-top: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
    & > div {
      &:first-child {
        background: rgb(var(--v-theme-bg));
      }
      &:not(:first-child) {
        font-weight: 500;
      }
    }
  }
  &__body {
    & > div {
      padding: $padding;
    }
    &:nth-last-child(2) {
      border-bottom: none !important;
    }
  }
  &__date {
    color: rgb(var(--v-theme-gray));
    border-right: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
    height: 57px;
    display: inline-flex;
    width: 100%;
    align-items: center;
    position: sticky;
    left: 0;
    background: white;
  }
}
.page-stats {
  .loader {
    position: fixed;
    top: 0;
    left: 250px;
    width: calc(100% - 250px) !important;
    background: rgba(white, 0.8);
    z-index: 9;
  }
}
</style>

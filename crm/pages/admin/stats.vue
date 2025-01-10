<script lang="ts" setup>
import type { StatsDialog } from '#build/components'
import { mdiDownload, mdiTune } from '@mdi/js'
import { clone } from 'rambda'
import { MetricComponents, type StatsMetric, type StatsParams } from '~/components/Stats/Metrics'

const statsDialog = ref<InstanceType<typeof StatsDialog>>()

const params = ref<StatsParams>({
  metrics: [],
  mode: 'day',
  date: null,
  date_from: null,
})

// сохраняем параметры ответа сервера, чтобы не зависеть от текущих параметров
// (например, если снесли метрику или изменили режим на "по годам", чтобы не менялось форматирование)
const responseParams = ref<StatsParams>()

const loading = ref(false)
const exporting = ref(false)

const items = ref<StatsListResource[]>([])
const totals = ref<number[]>([])
const page = ref<number>(0)

let scrollContainer: HTMLElement | null = null

function onGo(p: StatsParams) {
  params.value = clone(p)
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
      responseParams.value = clone(params.value)
      items.value = data.value.data
      totals.value = data.value.totals
    }
    else {
      items.value = items.value.concat(data.value.data)
    }
  }
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
        page: null,
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
  if (!scrollContainer || loading.value || !items.value.length) {
    return
  }
  const { scrollTop, scrollHeight, clientHeight } = scrollContainer
  const scrollPosition = scrollTop + clientHeight
  const scrollThreshold = scrollHeight * 0.9

  if (scrollPosition >= scrollThreshold) {
    loadMore()
  }
}

onMounted(() => {
  scrollContainer = document.documentElement.querySelector('main')
  scrollContainer?.addEventListener('scroll', onScroll)
})

onUnmounted(() => {
  scrollContainer?.removeEventListener('scroll', onScroll)
})
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="loading" />
  </v-fade-transition>
  <div class="table table-stats">
    <div class="table-stats__header">
      <div class="table-stats__header-mode">
        <v-btn :icon="mdiTune" :size="48" variant="text" @click="statsDialog?.open()" />
        <v-btn
          :icon="mdiDownload"
          :size="48"
          :disabled="!(responseParams && responseParams.metrics.length)"
          :loading="exporting"
          variant="text"
          @click="exportDownload()"
        />
      </div>
      <template v-if="responseParams">
        <div
          v-for="(metric, index) in responseParams.metrics"
          :key="index"
          :style="getWidth(metric)"
          class="table-stats__header-metric"
        >
          <span>
            {{ metric.label }}
          </span>
        </div>
      </template>
    </div>
    <template v-if="responseParams">
      <div v-for="{ date, values } in items" :key="date" class="table-stats__body">
        <div class="table-stats__date">
          {{ formatDateMode(date, responseParams.mode) }}
        </div>
        <div
          v-for="(value, index) in values"
          :key="index"
          :class="`text-${responseParams.metrics[index].color}`"
          :style="getWidth(responseParams.metrics[index])"
        >
          {{ value ? formatPrice(value) : '' }}
        </div>
      </div>
      <div class="table-stats__footer table-stats__body">
        <div class="table-stats__date">
          итого
        </div>
        <div
          v-for="(total, index) in totals"
          :key="index"
          :style="getWidth(responseParams.metrics[index])"
          :class="`text-${responseParams.metrics[index].color}`"
        >
          <span>
            {{ total ? formatPrice(total) : '' }}
          </span>
        </div>
      </div>
    </template>
  </div>
  <StatsDialog ref="statsDialog" @go="onGo" />
</template>

<style lang="scss">
.table-stats {
  $padding: 0 20px;
  width: max-content;
  min-width: 100%;
  & > div {
    gap: 0 !important;
    padding: 0 !important;
    & > div {
      &:first-child {
        width: 150px;
      }
    }
  }
  &__header {
    position: sticky !important;
    top: 0;
    text-transform: lowercase;
    background: white;
    z-index: 1;
    &-mode {
      height: 57px;
      border-right: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
      padding: $padding;
      display: inline-flex;
      align-items: center;
      position: sticky;
      left: 0;
      background: white;
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
    background: white;
    z-index: 1;
    border-top: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
    & > div {
      &:not(:first-child) {
        font-weight: 500;
      }
    }
  }
  &__body {
    & > div {
      padding: $padding;
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
    left: 255px;
    width: calc(100% - 255px) !important;
    background: rgba(white, 0.8);
    z-index: 9;
  }
}
</style>

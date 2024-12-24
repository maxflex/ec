<script lang="ts" setup>
import type { StatsDialog } from '#build/components'
import { mdiTune } from '@mdi/js'
import { clone } from 'rambda'
import { MetricComponents, type StatsMetric, type StatsParams } from '~/components/Stats/Metrics'

const statsDialog = ref<InstanceType<typeof StatsDialog>>()

const params = ref<StatsParams>({
  metrics: [],
  mode: 'day',
  date: today(),
})

// сохраняем параметры ответа сервера, чтобы не зависеть от текущих параметров
// (например, если снесли метрику или изменили режим на "по годам", чтобы не менялось форматирование)
const responseParams = ref<StatsParams>()

const loading = ref(false)

const items = ref<StatsListResource[]>([])
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
  const { data } = await useHttp<StatsListResource[]>(
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
      items.value = data.value
    }
    else {
      items.value = items.value.concat(data.value)
    }
  }
  loading.value = false
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
      <template v-if="responseParams">
        <div class="table-stats__header-mode">
          {{ StatsModeLabel[responseParams.mode] }}
        </div>
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
      <div class="table-stats__header-apply">
        <v-btn :icon="mdiTune" :size="48" @click="statsDialog?.open()" />
      </div>
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
    </template>
  </div>
  <StatsDialog ref="statsDialog" @go="onGo" />
</template>

<style lang="scss" scoped>
.table-stats {
  $padding: 0 20px;
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
    position: sticky;
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
    &-apply {
      padding: $padding;
      text-align: right;
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

<script lang="ts" setup>
import { clone } from 'rambda'
import { VueDraggableNext } from 'vue-draggable-next'
import type { StatsMetricFiltersDialog, StatsMetricSelectorDialog } from '#build/components'
import Metrics from '~/components/Stats/Metrics'

const metricSelectorDialog = ref<InstanceType<typeof StatsMetricSelectorDialog>>()
const metricFiltersDialog = ref<InstanceType<typeof StatsMetricFiltersDialog>>()
const mode = ref<StatsMode>('day')
const metrics = ref<MetricItem[]>([])
const loading = ref(false)
// сохраняем параметры ответа сервера, чтобы не зависеть от текущих параметров
// (например, если снесли метрику или изменили режим на "по годам", чтобы не менялось форматирование)
const responseParams = ref({
  mode: mode.value,
  metrics: clone(metrics.value),
})
const items = ref<StatsListResource[]>([])
const page = ref<number>(0)

const isDragging = ref(false)

let scrollContainer: HTMLElement | null = null

function onMetricsSelected(items: StatsMetric[]) {
  for (const metric of items) {
    metrics.value.push({
      metric,
      filters: {},
    })
  }
}

function deleteMetric(index: number, event: MouseEvent) {
  event.stopPropagation()
  metrics.value.splice(index, 1)
}

function onFiltersApply(index: number, f: any) {
  metrics.value[index].filters = f
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
          page: page.value,
          mode: mode.value,
          metrics: metrics.value,
        },
      },
  )
  if (data.value) {
    if (page.value === 1) {
      responseParams.value = {
        mode: mode.value,
        metrics: clone(metrics.value),
      }
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

function hasFilters(m: MetricItem): boolean {
  return Object.keys(m.filters).length > 0
}

function getWidth(m: MetricItem) {
  const { width } = Metrics[m.metric]
  return {
    width: `${width || 90}px`,
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
  <div :class="{ 'table-stats--loading': loading }" class="table table-stats">
    <div class="table-stats__header">
      <div class="table-stats__header-mode">
        <UiDropdown
          v-model="mode"
          :items="selectItems(StatsModeLabel)"
        />
      </div>
      <VueDraggableNext
        v-model="metrics"
        :animation="200"
        :class="{
          'table-stats__header-metrics--dragging': isDragging,
        }"
        class="table-stats__header-metrics"
        direction="horizontal"
        @end="() => (isDragging = false)"
        @start="() => (isDragging = true)"
      >
        <div
          v-for="(metric, index) in metrics"
          :key="index"
          :class="{
            'table-stats__header-metric--has-items': hasFilters(metric),
          }"
          :style="getWidth(metric)"
          class="table-stats__header-metric"
          @click="metricFiltersDialog?.open(metric, index)"
        >
          <span>
            {{ Metrics[metric.metric].label }}
          </span>
          <v-icon icon="$close" @click="deleteMetric(index, $event)" />
        </div>
      </VueDraggableNext>
      <div class="table-stats__header-add" @click="metricSelectorDialog?.open()">
        <v-icon icon="$plus" />
      </div>
      <div class="table-stats__header-apply">
        <v-btn :loading="page === 1 && loading" color="primary" @click="loadData()">
          применить
        </v-btn>
      </div>
    </div>
    <div v-for="{ date, values } in items" :key="date" class="table-stats__body">
      <div class="text-gray">
        {{ formatDateMode(date, responseParams.mode) }}
      </div>
      <div
        v-for="(value, index) in values"
        :key="index"
        :class="{ 'text-error': value < 0 }"
        :style="getWidth(responseParams.metrics[index])"
      >
        {{ value ? formatPrice(value) : '' }}
      </div>
    </div>
  </div>
  <StatsMetricSelectorDialog ref="metricSelectorDialog" @select="onMetricsSelected" />
  <StatsMetricFiltersDialog ref="metricFiltersDialog" @apply="onFiltersApply" />
</template>

<style lang="scss" scoped>
.table-stats {
  $padding: 0 20px;
  &--loading {
    .table-stats__body {
      opacity: 0.5;
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
  &__header {
    position: sticky;
    top: 0;
    text-transform: lowercase;
    background: white;
    z-index: 1;
    &-mode {
      padding: $padding;
    }
    &-metric {
      position: relative;
      line-height: 20px;
      padding: $padding;
      &--has-items {
        span {
          position: relative;
          &:after {
            content: '';
            $size: 8px;
            height: $size;
            width: $size;
            border-radius: 50%;
            position: absolute;
            right: -10px;
            top: 0;
            background: rgb(var(--v-theme-error));
          }
        }
      }
      .v-icon {
        position: absolute;
        right: 0;
        top: 0;
        font-size: 20px;
        color: rgb(var(--v-theme-bg2));
        opacity: 0;
        &:hover {
          color: rgb(var(--v-theme-error));
        }
      }
    }
    &-add,
    &-metric {
      align-self: stretch;
      display: flex;
      align-items: center;
      cursor: pointer;
      user-select: none;
    }
    &-metrics {
      display: flex;
      align-self: stretch;
      &:not(.table-stats__header-metrics--dragging) {
        & > div:hover {
          background: rgb(var(--v-theme-bg));
          .v-icon {
            opacity: 1;
          }
        }
      }
    }
    &-add {
      width: 60px !important;
      justify-content: center;
      color: rgb(var(--v-theme-secondary));
      &:hover {
        background: rgb(var(--v-theme-bg));
      }
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
}
.sortable-drag {
  .v-icon {
    display: none;
  }
}
.sortable-ghost {
  background: rgba(var(--v-theme-secondary), 0.1);
}
</style>

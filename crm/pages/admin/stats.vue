<script lang="ts" setup>
import { clone } from 'rambda'
import type { StatsMetricFiltersDialog, StatsMetricSelectorDialog } from '#build/components'
import Metrics from '~/components/Stats/Metrics'

const metricSelectorDialog = ref<InstanceType<typeof StatsMetricSelectorDialog>>()
const metricFiltersDialog = ref<InstanceType<typeof StatsMetricFiltersDialog>>()
const mode = ref<StatsMode>('day')
const metrics = ref<StatsMetric[]>([])
const loading = ref(false)
// сохраняем параметры ответа сервера, чтобы не зависеть от текущих параметров
// (например, если снесли метрику или изменили режим на "по годам", чтобы не менялось форматирование)
const responseParams = ref({
  mode: mode.value,
  metrics: clone(metrics.value),
})
const items = ref<StatsListResource[]>([])
const filters = ref<object[]>([])
const page = ref<number>(0)
let scrollContainer: HTMLElement | null = null

function onMetricsSelected(items: StatsMetric[]) {
  for (const metric of items) {
    metrics.value.push(metric)
    filters.value.push({})
  }
}

function deleteMetric(index: number, event: MouseEvent) {
  event.stopPropagation()
  filters.value.splice(index, 1)
  metrics.value.splice(index, 1)
}

function onFiltersApply(index: number, f: any) {
  console.log('Filters selected', f)
  filters.value[index] = f
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
          items: filters.value.map((filters, i) => ({
            filters,
            metric: metrics.value[i],
          })),
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

function hasFilters(index: number): boolean {
  return Object.keys(filters.value[index]).length > 0
}

function getWidth(metric: StatsMetric) {
  const { width } = Metrics[metric]
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
      <div>
        <UiDropdown
          v-model="mode"
          :items="selectItems(StatsModeLabel)"
        />
      </div>
      <div
        v-for="(metric, index) in metrics"
        :key="index"
        :class="{
          'table-stats__metric-label--has-items': hasFilters(index),
        }"
        :style="getWidth(metric)"
        class="table-stats__metric-label"
        @click="metricFiltersDialog?.open(metric, index, filters[index])"
      >
        <span>
          {{ Metrics[metric].label }}
        </span>
        <v-icon icon="$close" @click="deleteMetric(index, $event)" />
      </div>
      <div class="table-stats__add" @click="metricSelectorDialog?.open()">
        <v-icon icon="$plus" />
      </div>
      <div class="text-right">
        <v-btn :loading="page === 1 && loading" color="primary" @click="loadData()">
          применить
        </v-btn>
      </div>
    </div>
    <div v-for="{ date, metrics } in items" :key="date" class="table-stats__metrics">
      <div class="text-gray">
        {{ formatDateMode(date, responseParams.mode) }}
      </div>
      <div
        v-for="(metricValue, index) in metrics"
        :key="index"
        :class="{ 'text-error': metricValue < 0 }"
        :style="getWidth(responseParams.metrics[index])"
      >
        {{ metricValue ? formatPrice(metricValue) : '' }}
      </div>
    </div>
  </div>
  <StatsMetricSelectorDialog ref="metricSelectorDialog" @select="onMetricsSelected" />
  <StatsMetricFiltersDialog ref="metricFiltersDialog" @apply="onFiltersApply" />
</template>

<style lang="scss" scoped>
.table-stats {
  &--loading {
    .table-stats__metrics {
      opacity: 0.5;
    }
  }
  & > div {
    gap: 0 !important;
    padding: 0 !important;
    & > div {
      padding: 0 20px;
      //width: 100px;
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
    & > div {
      &:not(:first-child):not(:last-child) {
        align-self: stretch;
        display: flex;
        align-items: center;
        cursor: pointer;
        user-select: none;
        &:hover {
          background: rgb(var(--v-theme-bg));
        }
      }
    }
  }
  &__metric-label {
    position: relative;
    line-height: 20px;
    &:hover {
      background: rgb(var(--v-theme-bg));
      .v-icon {
        opacity: 1;
      }
    }
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
  &__add {
    width: 60px !important;
    justify-content: center;
    color: rgb(var(--v-theme-secondary));
  }
}
</style>

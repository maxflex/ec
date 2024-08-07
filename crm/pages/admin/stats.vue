<script lang="ts" setup>
import type { StatsMetricFiltersDialog, StatsMetricSelectorDialog } from '#build/components'
import Metrics from '~/components/Stats/Metrics'

const metricSelectorDialog = ref<InstanceType<typeof StatsMetricSelectorDialog>>()
const metricFiltersDialog = ref<InstanceType<typeof StatsMetricFiltersDialog>>()
const selectedMetrics = ref<StatsMetric[]>([])
const loading = ref(false)
const mode = ref<StatsMode>('day')
const items = ref<StatsListResource[]>([])
const filters = ref<object[]>([])
const page = ref<number>(0)
let scrollContainer: HTMLElement | null = null

function onMetricsSelected(metrics: StatsMetric[]) {
  for (const metric of metrics) {
    selectedMetrics.value.push(metric)
    filters.value.push({})
  }
}

function onFiltersApply(index: number, f: any) {
  console.log('Filters selected', f)
  filters.value[index] = f
}

async function loadData() {
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
            metric: selectedMetrics.value[i],
          })),
        },
      },
  )
  if (data.value) {
    items.value = page.value === 1 ? data.value : items.value.concat(data.value)
  }
  loading.value = false
}

function reloadData() {
  page.value = 0
  if (scrollContainer) {
    scrollContainer.scrollTop = 0
  }
  loadData()
}

function onScroll() {
  if (!scrollContainer || loading.value || !items.value.length) {
    return
  }
  const { scrollTop, scrollHeight, clientHeight } = scrollContainer
  const scrollPosition = scrollTop + clientHeight
  const scrollThreshold = scrollHeight * 0.9

  if (scrollPosition >= scrollThreshold) {
    loadData()
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
        v-for="(metric, index) in selectedMetrics"
        :key="index"
        @click="metricFiltersDialog?.open(metric, index)"
      >
        <span
          :class="{
            'table-stats__metric-label--has-items': Object.keys(filters[index]).length > 0,
          }"
          class="table-stats__metric-label"
        >
          {{ filterTruncate(Metrics[metric].label, 14) }}
        </span>
      </div>
      <div class="table-stats__add" @click="metricSelectorDialog?.open()">
        <v-icon icon="$plus" />
      </div>
      <div class="text-right">
        <v-btn :loading="page === 1 && loading" color="primary" @click="reloadData()">
          применить
        </v-btn>
      </div>
    </div>
    <div v-for="{ date, metrics } in items" :key="date" class="table-stats__metrics">
      <div class="text-gray">
        {{ formatDate(date) }}
      </div>
      <div v-for="(metricValue, index) in metrics" :key="index" :class="{ 'text-error': metricValue < 0 }">
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
      width: 100px;
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
    text-wrap: balance;
    &--has-items {
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
  &__add {
    width: 60px !important;
    justify-content: center;
    color: rgb(var(--v-theme-secondary));
  }
}
</style>

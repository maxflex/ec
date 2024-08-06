<script lang="ts" setup>
import { clone } from 'rambda'
import type { StatsMetricsDialog } from '#build/components'
import Metrics from '~/components/Stats/Metrics'

const { dialog, width } = useDialog('default')

const metrics = ref<StatsMetric[]>([])
const metricsDialog = ref<InstanceType<typeof StatsMetricsDialog>>()
const selectedMetric = ref<StatsMetric>('RequestsMetric')

function onSelect(items: StatsMetric[]) {
  metrics.value = clone(items)
}

function open(metric: StatsMetric) {
  selectedMetric.value = metric
  dialog.value = true
}

function saveFilters() {
  dialog.value = false
}

const MetricComponent = computed(() => Metrics[selectedMetric.value])
</script>

<template>
  <div class="table table-stats">
    <div>
      <div>
        <v-btn :size="48" icon="$plus" @click="metricsDialog?.open()" />
      </div>
      <div
        v-for="metric in metrics" :key="metric" class="tabs-item text-truncate"
        style="width: 100px" @click="open(metric)"
      >
        {{ Metrics[metric].label }}
      </div>
      <div class="text-right">
        <v-btn color="primary">
          применить
        </v-btn>
      </div>
    </div>
  </div>
  <StatsMetricsDialog ref="metricsDialog" @select="onSelect" />

  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        {{ MetricComponent.label }}
        <v-btn :size="48" icon="$save" variant="text" @click="saveFilters()" />
      </div>
      <div class="dialog-body">
        <component :is="MetricComponent" />
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss" scoped>
.table-stats {
  & > div {
    &:first-child {
      text-transform: lowercase;
    }
  }
  .tabs-item {
    text-align: center;
    border-radius: 10px;
    padding: 12px 8px !important;
  }
}
</style>

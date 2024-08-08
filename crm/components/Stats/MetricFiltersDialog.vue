<script lang="ts" setup>
import { clone, isNotNil, pickBy } from 'rambda'
import Metrics from '~/components/Stats/Metrics'

const emit = defineEmits<{
  apply: [index: number, filters: object]
}>()
const { dialog, width } = useDialog('default')
const selectedMetric = ref<StatsMetric>('RequestsMetric')
const metricComponentRef = ref()
let index: number = -1

function open(metric: StatsMetric, i: number, preFilters: object = {}) {
  selectedMetric.value = metric
  index = i
  dialog.value = true
  nextTick(() => {
    metricComponentRef.value.filters = clone(preFilters)
  })
}

function onFiltersApply() {
  dialog.value = false
  const filters = pickBy<any, object>(isNotNil, metricComponentRef.value.filters)
  emit('apply', index, filters)
}

const MetricComponent = computed(() => Metrics[selectedMetric.value])

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        {{ MetricComponent.label }}
        <v-btn :size="48" icon="$save" variant="text" @click="onFiltersApply()" />
      </div>
      <div class="dialog-body">
        <component :is="MetricComponent" ref="metricComponentRef" />
      </div>
    </div>
  </v-dialog>
</template>

<script lang="ts" setup>
import Metrics from '~/components/Stats/Metrics'

const emit = defineEmits<{
  apply: [index: number, f: any]
}>()
const { dialog, width } = useDialog('default')
const selectedMetric = ref<StatsMetric>('RequestsMetric')
const metricComponentRef = ref()
let index: number = -1

function open(metric: StatsMetric, i: number) {
  selectedMetric.value = metric
  index = i
  dialog.value = true
}

function onFiltersApply() {
  dialog.value = false
  emit('apply', index, metricComponentRef.value.filters)
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

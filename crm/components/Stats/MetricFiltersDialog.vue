<script lang="ts" setup>
import { clone, isNotNil, pickBy } from 'rambda'
import { MetricColors, MetricComponents, type StatsMetric } from '~/components/Stats/Metrics'

const emit = defineEmits<{
  save: [index: number, m: StatsMetric]
}>()

const { dialog, width } = useDialog('default')
const item = ref<StatsMetric>({
  metric: 'RequestsMetric',
  color: 'black',
  label: '',
  filters: {},
})
const metricComponentRef = ref()
let index: number = -1

function open(m: StatsMetric, i: number) {
  dialog.value = true
  item.value = clone(m)
  index = i
  nextTick(() => metricComponentRef.value.filters = clone(m.filters))
}

function save() {
  dialog.value = false
  // const filters = pickBy<any, object>(isNotNil, metricComponentRef.value.filters)
  item.value.filters = pickBy<any, object>(isNotNil, metricComponentRef.value.filters)
  emit('save', index, item.value)
}

const CurrentMetricComponent = computed(() => MetricComponents[item.value.metric])

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        {{ CurrentMetricComponent.label }}
        <v-btn :size="48" icon="$save" variant="text" @click="save()" />
      </div>
      <div class="dialog-body">
        <div>
          <v-text-field v-model="item.label" label="Заголовок">
            <template #append-inner>
              <UiToggler
                v-model="item.color"
                :items="selectItems(MetricColors)"
                :class="`stats-color bg-${item.color}`"
              />
            </template>
          </v-text-field>
        </div>
        <component :is="CurrentMetricComponent" ref="metricComponentRef" />
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.stats-color {
  $size: 18px;
  height: $size;
  width: $size;
  display: inline-block;
  border-radius: 50%;
  cursor: pointer;
  color: transparent !important;
  transition: background-color linear 0.1s;
}
</style>

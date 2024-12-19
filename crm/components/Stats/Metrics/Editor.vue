<script lang="ts" setup>
import { clone, isNotNil, pickBy } from 'rambda'
import { MetricColors, MetricComponents, type StatsMetric } from '~/components/Stats/Metrics/index'

const emit = defineEmits<{
  save: [m: StatsMetric]
  savePreset: [m: StatsMetric]
  delete: []
}>()

const item = ref<StatsMetric>()
const metricComponentRef = ref()
const savingPreset = ref(false)

function open(m: StatsMetric) {
  item.value = clone(m)
  nextTick(() => metricComponentRef.value.filters = clone(m.filters))
}

function save() {
  cleanUpFilters()
  emit('save', clone(item.value!))
}

async function savePreset() {
  savingPreset.value = true
  cleanUpFilters()
  const { data } = await useHttp<StatsMetric>(
    `stats-presets`,
    {
      method: 'post',
      body: { ...item.value },
    },
  )
  if (data.value) {
    setTimeout(() => {
      emit('savePreset', data.value!)
      savingPreset.value = false
    }, 500)
  }
  else {
    savingPreset.value = false
  }
}

function cleanUpFilters() {
  if (!item.value) {
    return
  }
  item.value.filters = pickBy<any, object>(isNotNil, metricComponentRef.value.filters)
}

const CurrentMetricComponent = computed(() => item.value ? MetricComponents[item.value.metric] : null)

defineExpose({ open })
</script>

<template>
  <template v-if="item">
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
    <div>
      <v-btn
        color="primary"
        size="x-large"
        block
        @click="save()"
      >
        применить
      </v-btn>
    </div>

    <div style="display: flex; flex-direction: column; gap: 10px">
      <div>
        <v-btn variant="text" block :loading="savingPreset" @click="savePreset()">
          сохранить в пресет
        </v-btn>
      </div>
      <div>
        <v-btn variant="text" block @click="emit('delete')">
          удалить
        </v-btn>
      </div>
    </div>
  </template>
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

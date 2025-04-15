<script lang="ts" setup>
import type { StatsMetric } from '~/components/Stats/Metrics/index'
import { mdiEyeOffOutline } from '@mdi/js'
import { clone, isNil, pickBy } from 'lodash'
import { MetricColors, MetricComponents } from '~/components/Stats/Metrics/index'

const emit = defineEmits<{
  update: [m: StatsMetric]
}>()

const item = ref<StatsMetric>()
const metricComponentRef = ref()

function open(m: StatsMetric) {
  item.value = m
  // загрузить фильтры в компонент
  nextTick(() => metricComponentRef.value.filters = clone(m.filters))
}

watch(() => metricComponentRef.value?.filters, (newVal, oldVal) => {
  if (newVal === undefined || oldVal === undefined || !item.value) {
    return
  }
  item.value.filters = pickBy(newVal, val => !isNil(val))
  emit('update', item.value)
}, {
  deep: true,
})

const CurrentMetricComponent = computed(() => item.value ? MetricComponents[item.value.metric] : null)

defineExpose({ open })
</script>

<template>
  <template v-if="item">
    <div class="metric-editor">
      <v-table>
        <thead>
          <tr>
            <th>
              {{ CurrentMetricComponent?.label }}
            </th>
          </tr>
        </thead>
      </v-table>
      <v-text-field
        :key="CurrentMetricComponent"
        v-model="item.label"
        label="Заголовок"
      >
        <template #append-inner>
          <v-icon
            :icon="mdiEyeOffOutline"
            class="metric-editor__hide"
            :class="{
              'metric-editor__hide--hidden': item.hidden,
            }"
            @click="item.hidden = !item.hidden"
          ></v-icon>
          <UiToggler
            v-model="item.color"
            :items="selectItems(MetricColors)"
            :class="`metric-editor__color bg-${item.color}`"
          />
        </template>
      </v-text-field>
    </div>
    <component :is="CurrentMetricComponent" ref="metricComponentRef" />
  </template>
</template>

<style lang="scss">
.metric-editor {
  &__color {
    $size: 16px;
    height: $size;
    width: $size;
    display: inline-block;
    border-radius: 50%;
    cursor: pointer;
    color: transparent !important;
    transition: background-color linear 0.1s;
  }
  & .v-table__wrapper {
    position: relative;
    top: -30px;
    width: calc(100% + 40px) !important;
    left: -20px !important;
  }
  &__hide {
    margin-right: 10px;
    color: rgb(var(--v-theme-gray));
    &:hover {
      opacity: 1;
    }
    &--hidden {
      color: rgb(var(--v-theme-error));
      opacity: 1 !important;
    }
  }
}
</style>

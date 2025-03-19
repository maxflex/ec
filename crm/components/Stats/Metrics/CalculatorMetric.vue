<script lang="ts">
import type { StatsParams } from '.'
import { mdiMinus, mdiMultiplication, mdiPlus, mdiSlashForward } from '@mdi/js'

const RoundFilterLabel = {
  '3': '1000',
  '2': '100',
  '1': '10',
  '0': 'до целых',
  '-1': '0,1',
  '-2': '0,01',
  '-3': '0,001',
} as const

const MetricOperatorLabel = {
  '+': { label: 'плюс', icon: mdiPlus },
  '-': { label: 'минус', icon: mdiMinus },
  '/': { label: 'разделить', icon: mdiSlashForward },
  '*': { label: 'умножить', icon: mdiMultiplication },
} as const

type MetricOperator = keyof typeof MetricOperatorLabel
type RoundFilter = keyof typeof RoundFilterLabel

interface AddedMetric {
  id?: number
  operator: MetricOperator
}

interface Filters {
  round: RoundFilter
  metrics: AddedMetric[]
}

const filterDefaults: Filters = {
  // @ts-ignore
  round: 0,
  metrics: [
    {
      operator: '+',
    },
    {
      operator: '+',
    },
  ],
}

export default {
  label: 'Калькулятор',
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
const params = inject<Ref<StatsParams>>('params')
const filters = ref<Filters>({ ...filterDefaults })

const metrics = computed(() => params?.value.metrics.filter(m => m.metric !== 'CalculatorMetric').map(m => ({
  value: m.id,
  title: m.label,
})))

function add() {
  filters.value.metrics.push({
    operator: '+',
  })
}

defineExpose({ filters })
</script>

<template>
  <div>
    <v-select
      v-model="filters.round"
      label="Округление"
      :items="selectItems(RoundFilterLabel)"
      :menu-props="{
        maxHeight: 999,
      }"
    />
  </div>
  <div>
    <div v-for="(m, index) in filters.metrics" :key="index" class="calculator-metric">
      <div>
        <v-select
          v-model="m.id"
          :items="metrics"
          label="Метрика"
        />
      </div>
      <div class="calculator-metric__operator">
        <v-menu v-if="index < filters.metrics.length - 1" offset="6 46">
          <template #activator="{ props }">
            <v-btn v-bind="props" :size="48" color="primary" :icon="MetricOperatorLabel[m.operator].icon">
            </v-btn>
          </template>
          <v-list>
            <v-list-item
              v-for="(item, operator) in MetricOperatorLabel"
              :key="operator"
              @click="() => m.operator = operator"
            >
              <template #prepend>
                <v-icon :icon="item.icon" />
              </template>
              {{ item.label }}
            </v-list-item>
          </v-list>
        </v-menu>
      </div>
    </div>
  </div>
  <v-btn color="primary" :disabled="metrics.length === 0" @click="add()">
    добавить метрику
  </v-btn>
</template>

<style lang="scss">
.calculator-metric {
  &__operator {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 0;
  }
}
</style>

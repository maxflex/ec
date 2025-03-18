<script lang="ts">
import type { StatsMetric } from '.'

const RoundFilterLabel = {
  '3': '+3',
  '2': '+2',
  '1': '+1',
  '0': 'не округлять',
  '-1': '-1',
  '-2': '-2',
  '-3': '-3',
} as const

type RoundFilter = keyof typeof RoundFilterLabel

interface Filters {
  numerator?: number
  denominator?: number
  round: RoundFilter
}

const filterDefaults: Filters = {
  // @ts-ignore
  round: 0,
}

export default {
  label: 'Доля',
  filters: { ...filterDefaults },
  special: true,
}
</script>

<script lang="ts" setup>
const metrics = inject<StatsMetric[]>('metrics')
const filters = ref<Filters>({ ...filterDefaults })

const items = metrics!.filter(m => m.metric !== 'PercentMetric').map(m => ({
  value: m.id,
  title: m.label,
}))

defineExpose({ filters })
</script>

<template>
  <div>
    <v-select
      v-model="filters.numerator"
      label="Числитель"
      :items="items"
    />
  </div>
  <div>
    <v-select
      v-model="filters.denominator"
      label="Знаменатель"
      :items="items"
    />
  </div>
  <div>
    <v-select
      v-model="filters.round"
      label="Округление"
      :items="selectItems(RoundFilterLabel)"
    />
  </div>
</template>

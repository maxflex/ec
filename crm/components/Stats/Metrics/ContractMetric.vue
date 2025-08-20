<script lang="ts">
const VersionFilterLabel = {
  firstInClient: 'первая в клиенте',
  firstInContract: 'первая в договоре',
  recurringFirstInClient: 'повторные первые в клиенте',
  onlyChanges: 'только изменения версий',
} as const

type VersionFilter = keyof typeof VersionFilterLabel

interface Filters {
  aggregate: MetricAggregate
  year: Year[]
  company?: Company
  version?: VersionFilter
  direction: Direction[]
}

const filterDefaults: Filters = {
  aggregate: 'sum',
  year: [],
  direction: [],
}

export default {
  label: 'Договоры',
  width: 150,
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
</script>

<template>
  <div>
    <v-select v-model="filters.aggregate" :items="selectItems(MetricAggregateLabel)" label="Данные" />
  </div>
  <div>
    <UiMultipleSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.company"
      label="Компания"
      :items="selectItems(CompanyLabel)"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.version"
      label="Версия договора"
      :items="selectItems(VersionFilterLabel)"
    />
  </div>
  <div>
    <UiMultipleSelect v-model="filters.direction" :items="selectItems(DirectionLabel)" label="Направления" />
  </div>
</template>

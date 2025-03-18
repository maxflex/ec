<script lang="ts">
interface Filters {
  aggregate: MetricAggregate
  status: ReportStatus[]
  direction: Direction[]
}

const filterDefaults: Filters = {
  aggregate: 'sum',
  direction: [],
  status: [],
}

export default {
  label: 'Отчеты',
  filters: { ...filterDefaults } as Filters,
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
    <UiMultipleSelect
      v-model="filters.status"
      :items="selectItems(ReportStatusLabel)"
      label="Статус"
    />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.direction"
      :items="selectItems(DirectionLabel)"
      label="Направление"
    />
  </div>
</template>

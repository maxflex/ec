<script lang="ts">
interface Filters {
  aggregate: MetricAggregate
  status: ReportStatus[]
  direction: Direction[]
  delivery?: ReportDelivery
}

const filterDefaults: Filters = {
  aggregate: 'sum',
  direction: [],
  status: [],
}

export default {
  label: 'Отчеты',
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
  <div>
    <UiClearableSelect
      v-model="filters.delivery"
      :items="selectItems(ReportDeliveryLabel)"
      label="Доставка"
    />
  </div>
</template>

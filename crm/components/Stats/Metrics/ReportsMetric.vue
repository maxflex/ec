<script lang="ts" setup>
// const paymentTypeLabel = {
//   payments: 'только платежи',
//   returns: 'только возвраты',
//   diff: 'разница',
// }

interface Filters {
  year?: Year
  program?: Program
  status?: ReportStatus
  // type: 'payments' | 'returns'
}

const filters = ref<Filters>({})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Отчёты',
  filters: {},
}
</script>

<template>
  <div>
    <UiClearableSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" />
  </div>
  <div>
    <UiClearableSelect v-model="filters.program" :items="selectItems(ProgramLabel)" label="Программа" />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.status"
      :items="selectItems(ReportStatusLabel)"
      label="Статус"
    />
  </div>
</template>

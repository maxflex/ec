<script lang="ts" setup>
interface Filters {
  year?: Year
  program?: Program
  status?: ReportStatus
}

const filters = ref<Filters>({})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Отчёты (сумма в руб.)',
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
      label="Статус отчёта"
    />
  </div>
</template>

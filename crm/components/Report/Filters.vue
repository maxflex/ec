<script lang="ts" setup>
const model = defineModel<ReportFilters>({ required: true })
</script>

<template>
  <v-select
    v-model="model.year"
    label="Учебный год"
    :items="selectItems(YearLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.type"
    label="Тип"
    :items="yesNo('созданные', 'требуется отчет')"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.program"
    label="Программа"
    :items="selectItems(ProgramLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.status"
    label="Статус"
    :items="selectItems(ReportStatusLabel)"
    density="comfortable"
  />
</template>

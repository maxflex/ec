<script lang="ts" setup>
const model = defineModel<ReportTeacherFilters>({ required: true })
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
    :items="yesNo('созданные', 'требуется отчёт')"
    density="comfortable"
  />
</template>

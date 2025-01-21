<script lang="ts" setup>
const model = defineModel<ContractVersionFilters>({ required: true })
</script>

<template>
  <v-select
    v-model="model.year"
    label="Учебный год"
    :items="selectItems(YearLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.company"
    :items="selectItems(CompanyLabel)"
    density="comfortable"
    label="Тип"
  />
  <UiClearableSelect
    v-model="model.is_active"
    :items="[
      { value: 1, title: 'активная' },
    ]"
    density="comfortable"
    label="Версия"
  />
  <UiMultipleSelect
    v-model="model.direction"
    :items="selectItems(DirectionLabel)"
    label="Направления"
    density="comfortable"
  />
</template>

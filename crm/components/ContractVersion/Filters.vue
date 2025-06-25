<script lang="ts" setup>
export interface ContractVersionFilters {
  year: Year
  company?: Company
  is_active?: number
  direction: Direction[]
}

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
      { value: 0, title: 'первая' },
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

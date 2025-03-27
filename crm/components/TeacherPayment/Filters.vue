<script lang="ts" setup>
export interface TeacherPaymentFilters {
  year: Year
  method?: TeacherPaymentMethod
}

const model = defineModel<TeacherPaymentFilters>({ required: true })
</script>

<template>
  <v-select
    v-model="model.year"
    label="Учебный год"
    :items="selectItems(YearLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.method"
    label="Метод"
    :items="selectItems(TeacherPaymentMethodLabel)"
    density="comfortable"
  />
</template>

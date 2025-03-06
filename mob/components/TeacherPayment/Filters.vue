<script lang="ts" setup>
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

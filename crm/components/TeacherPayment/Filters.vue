<script lang="ts" setup>
export interface Filters {
  year: Year
  method?: TeacherPaymentMethod
}
const emit = defineEmits<{ (e: 'apply', filters: Filters): void }>()
const filters = ref(loadFilters<Filters>({
  year: currentAcademicYear(),
}))

watch(filters.value, () => {
  saveFilters(filters.value)
  emit('apply', filters.value)
})
</script>

<template>
  <div class="filters-inputs">
    <div>
      <v-select
        v-model="filters.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
    </div>
    <div>
      <UiClearableSelect
        v-model="filters.method"
        label="Метод"
        :items="selectItems(TeacherPaymentMethodLabel)"
        density="comfortable"
      />
    </div>
  </div>
</template>

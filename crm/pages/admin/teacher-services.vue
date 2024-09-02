<script setup lang="ts">
const filters = ref<{ year: Year }>({
  year: currentAcademicYear(),
})

const { items, loading, onFiltersApply } = useIndex<TeacherServiceResource>(
    `teacher-services`,
    {
      defaultFilters: {
        year: currentAcademicYear(),
      },
    },
)

watch(filters.value, onFiltersApply)
</script>

<template>
  <div class="filters">
    <div class="filters-inputs">
      <v-select v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" density="comfortable" />
    </div>
  </div>
  <UiLoader3 :loading="loading" />
  <TeacherServiceList :items="items" />
</template>

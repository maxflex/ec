<script setup lang="ts">
const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))
const { items, indexPageData } = useIndex<TeacherPaymentResource, YearFilters>(
    `teacher-payments`,
    filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
    </template>
    <TeacherPaymentList :items="items" />
  </UiIndexPage>
</template>

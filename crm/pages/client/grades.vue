<script setup lang="ts">
const filters = ref<YearFilters>({
  year: currentAcademicYear(),
})

const { items, indexPageData } = useIndex<GradeListResource, YearFilters>(
  `grades`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiYearSelector v-model="filters.year" density="comfortable" />
    </template>
    <GradeList :items="items" />
  </UiIndexPage>
</template>

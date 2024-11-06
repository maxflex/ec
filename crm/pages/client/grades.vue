<script setup lang="ts">
const filters = ref<YearFilters>({
  year: currentAcademicYear(),
})

const { items, indexPageData } = useIndex<QuartersGradesResource, YearFilters>(
  `grades`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiYearSelector v-model="filters.year" density="comfortable" />
    </template>
    <GradeListForClients :items="items" />
  </UiIndexPage>
</template>

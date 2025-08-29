<script setup lang="ts">
const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears } = useIndex<QuartersGradesResource>(
  `grades`,
  filters,
  {
    loadAvailableYears: true,
  },
)
</script>

<template>
  <UiPageTitle>
    Оценки
  </UiPageTitle>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <GradeList :items="items" />
  </UiIndexPage>
</template>

<script setup lang="ts">
const filters = ref<AvailableYearsFilter>({
  year: undefined,
})

const { items, indexPageData, availableYears } = useIndex<QuartersGradesResource, AvailableYearsFilter>(
  `grades`,
  filters,
  {
    loadAvailableYears: true,
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <GradeList :items="items" />
  </UiIndexPage>
</template>

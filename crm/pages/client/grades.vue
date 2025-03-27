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
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <GradeListForClients :items="items" />
  </UiIndexPage>
</template>

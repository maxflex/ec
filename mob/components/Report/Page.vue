<script setup lang="ts">
const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears } = useIndex<RealReport>(
  `reports`,
  filters,
  {
    loadAvailableYears: true,
  },
)
</script>

<template>
  <UiPageTitle>
    Отчёты
  </UiPageTitle>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <ReportList :items="items" />
  </UiIndexPage>
</template>

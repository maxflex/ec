<script setup lang="ts">
import type { RealReport } from '~/components/Report'

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
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <ReportListForClients :items="items" />
  </UiIndexPage>
</template>

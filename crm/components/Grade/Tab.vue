<script setup lang="ts">
const { clientId } = defineProps<{ clientId: number }>()
const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears } = useIndex<QuartersGradesResource>(
  `grades`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      client_id: clientId,
    },
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

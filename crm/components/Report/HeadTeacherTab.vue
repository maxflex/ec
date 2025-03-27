<script setup lang="ts">
/**
 * Вкладка "отчеты" в профиле клиента у классрука
 */

const { clientId } = defineProps<{
  clientId: number
}>()

const filters = useAvailableYearsFilter()

const { indexPageData, items, availableYears } = useIndex<ReportListResource>(
  `reports`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      client_id: clientId,
      requirement: 'created',
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <ReportListForHeadTeachers :items="items" />
  </UiIndexPage>
</template>

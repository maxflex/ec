<script setup lang="ts">
interface Filters {
  year?: undefined
  is_required?: number
}
const filters = ref<Filters>({
  year: undefined,
})

const { items, indexPageData, availableYears } = useIndex<ReportListResource>(
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
      <UiClearableSelect
        v-model="filters.is_required"
        label="Тип"
        :items="yesNo('только требования', 'только созданные')"
        density="comfortable"
      />
    </template>
    <ReportListForTeachers :items="items" />
  </UiIndexPage>
</template>

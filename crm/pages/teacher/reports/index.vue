<script setup lang="ts">
interface Filters {
  year: Year
  type?: number
}
const filters = ref<Filters>(loadFilters({
  year: currentAcademicYear(),
}))
const { items, indexPageData } = useIndex<ReportListResource, Filters>(
    `reports`,
    filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Год"
        density="comfortable"
      />
      <UiClearableSelect
        v-model="filters.type"
        label="Тип"
        :items="yesNo('созданные', 'требуется отчёт')"
        density="comfortable"
      />
    </template>
    <ReportList :items="items" />
  </UiIndexPage>
</template>

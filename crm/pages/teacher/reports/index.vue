<script setup lang="ts">
const filters = ref<{
  year: Year
  type?: number
}>({
  year: currentAcademicYear(),
})
const { items, indexPageData } = useIndex<ReportListResource>(
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

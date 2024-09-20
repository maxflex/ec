<script setup lang="ts">
const filters = ref<ReportFilters>(loadFilters({
  year: currentAcademicYear(),
}))
const { items, indexPageData } = useIndex<ReportListResource, ReportFilters>(`reports`, filters)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ReportFilters v-model="filters" />
    </template>
    <ReportList :items="items" />
  </UiIndexPage>
</template>

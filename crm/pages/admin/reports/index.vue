<script setup lang="ts">
import type { ReportFilters } from '~/components/Report/Filters.vue'

const filters = ref<ReportFilters>(usePersistentFilters().load({
  year: currentAcademicYear(),
}))
const { items, indexPageData } = useIndex<ReportListResource>(`reports`, filters)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ReportFilters v-model="filters" />
    </template>
    <ReportListForAdmins :items="items" />
  </UiIndexPage>
</template>

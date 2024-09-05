<script setup lang="ts">
import type { Filters } from '~/components/Report/Filters.vue'

const { items, indexPageData, onFiltersApply } = useIndex<GradeListResource, Filters>(`grades`)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <GradeFilters @apply="onFiltersApply" />
    </template>
    <GradeList :items="items" />
  </UiIndexPage>
</template>

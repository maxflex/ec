<script setup lang="ts">
import type { Filters } from '~/components/Report/Filters.vue'

const { items, loading, onFiltersApply } = useIndex<GradeListResource, Filters>(`grades`)
</script>

<template>
  <UiFilters>
    <GradeFilters @apply="onFiltersApply" />
  </UiFilters>
  <div>
    <UiLoader3 :loading="loading" />
    <GradeList :items="items" />
  </div>
</template>

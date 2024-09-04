<script setup lang="ts">
import type { Filters } from '~/components/Swamp/Filters.vue'

const { items, loading, onFiltersApply } = useIndex<SwampListResource, Filters>(`swamps`, {
  defaultFilters: {
    year: currentAcademicYear(),
  },
})
</script>

<template>
  <UiFilters>
    <SwampFilters @apply="onFiltersApply" />
  </UiFilters>
  <div>
    <UiLoader3 :loading="loading" />
    <SwampList :items="items" />
  </div>
</template>

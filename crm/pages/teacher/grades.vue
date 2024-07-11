<script setup lang="ts">
import type { Filters } from '~/components/Report/Filters.vue'

const { items, loading, onFiltersApply } = useIndex<GradeListResource, Filters>(`grades`)
</script>

<template>
  <div class="filters">
    <GradeFilters @apply="onFiltersApply" />
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <GradeList :items="items" />
  </div>
</template>

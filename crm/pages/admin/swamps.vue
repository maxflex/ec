<script setup lang="ts">
import type { Filters } from '~/components/Swamp/Filters.vue'

const { items, onFiltersApply, indexPageData } = useIndex<SwampListResource, Filters>(
    `swamps`,
    {
      defaultFilters: {
        year: currentAcademicYear(),
      },
    },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <SwampFilters @apply="onFiltersApply" />
    </template>
    <SwampList :items="items" />
  </UiIndexPage>
</template>

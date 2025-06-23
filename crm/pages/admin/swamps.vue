<script setup lang="ts">
import type { SwampCountsResource } from '~/components/Swamp'
import type { SwampFilters } from '~/components/Swamp/Filters.vue'

const filters = ref<SwampFilters>(loadFilters({
  year: currentAcademicYear(),
  program: [],
}))

const { items, indexPageData } = useIndex<SwampCountsResource>(
  `swamps`,
  filters,
  {
    staticFilters: {
      counts: 1,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <SwampFilters v-model="filters" />
    </template>
    <SwampCounts :items="items" />
  </UiIndexPage>
</template>

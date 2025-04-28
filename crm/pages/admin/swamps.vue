<script setup lang="ts">
import type { SwampFilters } from '~/components/Swamp/Filters.vue'

const filters = ref<SwampFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<SwampListResource>(
  `swamps`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <SwampFilters v-model="filters" />
    </template>
    <SwampList :items="items" />
  </UiIndexPage>
</template>

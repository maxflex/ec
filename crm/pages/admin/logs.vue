<script setup lang="ts">
import type { LogFilters } from '~/components/Log/Filters.vue'

const filters = ref<LogFilters>(loadFilters({}))
const { items, indexPageData } = useIndex<LogResource, LogFilters>(
  `logs`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <LogFilters v-model="filters" />
    </template>
    <LogList :items="items" />
  </UiIndexPage>
</template>

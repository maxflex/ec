<script setup lang="ts">
import type { Filters } from '~/components/Topic/Filters.vue'

const filters = ref<Filters>(loadFilters({
  year: currentAcademicYear(),
}))
const { items, indexPageData } = useIndex<TopicListResource, Filters>(`topics`, filters)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <TopicFilters v-model="filters" />
    </template>
    <TopicList :items="items" @updated="onUpdated" />
  </UiIndexPage>
</template>

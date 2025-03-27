<script setup lang="ts">
import type { TopicFilters } from '~/components/Topic/Filters.vue'

const filters = ref<TopicFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<TopicListResource>(`topics`, filters)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <TopicFilters v-model="filters" />
    </template>
    <TopicList :items="items" @updated="onUpdated" />
  </UiIndexPage>
</template>

<script setup lang="ts">
const filters = ref<TopicFilters>(loadFilters({
  year: currentAcademicYear(),
}))
const { items, indexPageData } = useIndex<TopicListResource, TopicFilters>(`topics`, filters)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <TopicFilters v-model="filters" />
    </template>
    <TopicList :items="items" @updated="onUpdated" />
  </UiIndexPage>
</template>

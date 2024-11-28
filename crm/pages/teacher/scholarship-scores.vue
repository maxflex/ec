<script setup lang="ts">
interface Filters {
  month: Month
}

const filters = ref<Filters>({
  month: new Date().getMonth() + 1 as Month,
})

const { items, indexPageData } = useIndex<ScholarshipScoreResource, Filters>(
  `scholarship-scores`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiMonthSelector v-model="filters.month" density="comfortable" />
    </template>
    <ScholarshipScoreList :items="items" />
  </UiIndexPage>
</template>

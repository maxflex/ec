<script setup lang="ts">
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

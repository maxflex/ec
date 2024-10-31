<script setup lang="ts">
const filters = ref<SwampFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<SwampListResource, SwampFilters>(
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

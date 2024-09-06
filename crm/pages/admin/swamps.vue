<script setup lang="ts">
import type { Filters } from '~/components/Swamp/Filters.vue'

const filters = ref<Filters>({
  year: currentAcademicYear(),
})

const { items, reloadData, indexPageData } = useIndex<SwampListResource, Filters>(
    `swamps`,
    {
      defaultFilters: filters.value,
    },
)

watch(filters, reloadData, { deep: true })
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <SwampFilters v-model="filters" />
    </template>
    <SwampList :items="items" />
  </UiIndexPage>
</template>

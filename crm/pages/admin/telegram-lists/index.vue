<script setup lang="ts">
import type { TelegramListFilters } from '~/components/TelegramList/Filters.vue'

const filters = ref<TelegramListFilters>(loadFilters({}))
const { items, indexPageData } = useIndex<TelegramListResource, TelegramListFilters>(
  `telegram-lists`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <TelegramListFilters v-model="filters" />
    </template>
    <TelegramList :items="items" />
  </UiIndexPage>
</template>

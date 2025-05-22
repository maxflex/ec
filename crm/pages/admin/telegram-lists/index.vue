<script setup lang="ts">
import type { TelegramListFilters } from '~/components/TelegramList/Filters.vue'

const filters = ref<TelegramListFilters>(loadFilters({}))
const { items, indexPageData } = useIndex<TelegramListResource, TelegramListFilters>(
  `telegram-lists`,
  filters,
)

function onDeleted(item: TelegramListResource) {
  const index = items.value.findIndex(e => e.id === item.id)
  items.value.splice(index, 1)
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <TelegramListFilters v-model="filters" />
    </template>
    <TelegramList :items="items" @deleted="onDeleted" />
  </UiIndexPage>
</template>

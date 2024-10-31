<script setup lang="ts">
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

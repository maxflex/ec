<script setup lang="ts">
const filters = ref<TelegramMessageFilters>(loadFilters({}))

const { items, indexPageData } = useIndex<TelegramMessageResource, TelegramMessageFilters>(
    `telegram-messages`,
    filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <TelegramMessageFilters v-model="filters" />
    </template>
    <TelegramMessageList :items="items" />
  </UiIndexPage>
</template>

<script setup lang="ts">
import type { TelegramMessageFilters } from '~/components/TelegramMessage/Filters.vue'

const filters = ref<TelegramMessageFilters>(loadFilters({}))

const { items, indexPageData } = useIndex<TelegramMessageResource>(
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

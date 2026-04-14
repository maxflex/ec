<script setup lang="ts">
import type { TelegramMessageFilters } from '~/components/TelegramMessage/Filters.vue'

const route = useRoute()
const { number: numberFromQuery } = route.query
const initialFilters: TelegramMessageFilters = numberFromQuery
  ? {
      // При явном number из URL игнорируем сохранённые фильтры из localStorage.
      number: formatPhone(numberFromQuery),
    }
  : usePersistentFilters().load<TelegramMessageFilters>({})

const filters = ref<TelegramMessageFilters>(initialFilters)

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

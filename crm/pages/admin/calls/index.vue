<script setup lang="ts">
import type { CallFilters } from '~/components/Call/Filters.vue'
import type { CallListResource } from '~/components/Call'

const route = useRoute()
const { number: numberFromQuery } = route.query
const initialFilters: CallFilters = numberFromQuery
  ? {
      // При явном number из URL игнорируем loadFilters из localStorage.
      number: formatPhone(numberFromQuery),
    }
  : loadFilters<CallFilters>({})

const filters = ref<CallFilters>(initialFilters)

watch(() => route.query.number, (newNumber) => {
  const number = typeof newNumber === 'string' && newNumber.length > 0
    ? formatPhone(newNumber)
    : undefined

  if (filters.value.number !== number) {
    filters.value.number = number
  }
})

const { items, indexPageData } = useIndex<CallListResource>(
  'calls',
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <CallFilters v-model="filters" />
    </template>

    <CallList :items="items" clickable />
  </UiIndexPage>
</template>

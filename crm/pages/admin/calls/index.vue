<script setup lang="ts">
import type { CallListResource } from '~/components/Call'
import type { CallFilters } from '~/components/Call/Filters.vue'

const route = useRoute()
const { number: numberFromQuery } = route.query
const defaultFilters: CallFilters = {
  call_status: [],
  caller_type: [],
  call_duration: [],
}

function normalizeArrayFilter<T extends string>(value: T[] | T | undefined): T[] {
  if (Array.isArray(value)) {
    return value.filter((item): item is T => typeof item === 'string')
  }

  if (typeof value === 'string' && value.length > 0) {
    return [value]
  }

  return []
}

// Нормализуем формат фильтров, чтобы корректно прочитать старые значения из localStorage.
function normalizeFilters(filters: Partial<CallFilters>): CallFilters {
  return {
    ...defaultFilters,
    ...filters,
    call_status: normalizeArrayFilter(filters.call_status),
    caller_type: normalizeArrayFilter(filters.caller_type),
    call_duration: normalizeArrayFilter(filters.call_duration),
  }
}

const initialFilters: CallFilters = numberFromQuery
  ? {
      // При явном number из URL игнорируем loadFilters из localStorage.
      ...normalizeFilters(defaultFilters),
      number: formatPhone(numberFromQuery),
    }
  : normalizeFilters(loadFilters<Partial<CallFilters>>(defaultFilters))

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

<style lang="scss">
.page-calls {
  .filters__inputs {
    max-width: 100%;
  }
}
</style>

<script setup lang="ts">
import type { CallFilters } from '~/components/Call/Filters.vue'
import type { CallListResource } from '~/components/CallApp'

const filters = ref<CallFilters>(loadFilters({}))

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

    <CallList :items="items" />
  </UiIndexPage>
</template>

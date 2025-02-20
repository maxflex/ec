<script setup lang="ts">
import type { SearchResultResource } from '.'
import { SearchResultsClientItem, SearchResultsRequestItem, SearchResultsTeacherItem } from '#components'

const { items } = defineProps<{
  items: SearchResultResource[]
}>()

function getComponent(item: SearchResultResource) {
  switch (item.entity_type) {
    case 'App\\Models\\Teacher':
      return SearchResultsTeacherItem

    case 'App\\Models\\Request':
      return SearchResultsRequestItem

    default:
      return SearchResultsClientItem
  }
}
</script>

<template>
  <div class="table table--padding">
    <template v-for="item in items" :key="`${item.entity_type}${item.id}`">
      <component :is="getComponent(item)" :item="item" />
    </template>
  </div>
</template>

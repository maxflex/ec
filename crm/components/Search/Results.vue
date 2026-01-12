<script setup lang="ts">
import type { SearchResultResource } from '.'
import { SearchResultsClientItem, SearchResultsContractItem, SearchResultsRequestItem, SearchResultsTeacherItem } from '#components'

const { items } = defineProps<{
  items: SearchResultResource[]
}>()

function getComponent(item: SearchResultResource) {
  switch (item.entity_type) {
    case 'App\\Models\\Teacher':
      return SearchResultsTeacherItem

    case 'App\\Models\\Request':
      return SearchResultsRequestItem

    case 'App\\Models\\Contract':
      return SearchResultsContractItem

    default:
      return SearchResultsClientItem
  }
}
</script>

<template>
  <Table class="search-results" hoverable>
    <template v-for="item in items" :key="`${item.entity_type}${item.id}`">
      <component :is="getComponent(item)" :item="item" />
    </template>
  </Table>
</template>

<style lang="scss">
.search-results {
  & > a {
    color: black !important;
  }
  & > * {
    cursor: pointer;
    & > div {
      pointer-events: none;
    }
    &:not(.request-item) {
      & > div {
        padding: 16px 0;
      }
    }
  }
}
</style>

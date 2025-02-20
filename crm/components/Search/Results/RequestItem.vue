<script setup lang="ts">
import type { SearchResultResource } from '..'
// иначе конфликт с Search/Results/RequestItem
import { RequestItem } from '#components'

const { item } = defineProps<{
  item: SearchResultResource
}>()
</script>

<template>
  <RequestItem :item="item.request!" />
</template>

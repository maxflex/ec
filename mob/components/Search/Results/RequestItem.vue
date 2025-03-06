<script setup lang="ts">
import type { SearchResultResource } from '..'
// иначе конфликт с Search/Results/RequestItem
import { RequestItem } from '#components'

const { item } = defineProps<{
  item: SearchResultResource
}>()

const router = useRouter()
</script>

<template>
  <RequestItem :item="item.request!" @click="router.push({ name: 'requests-id', params: { id: item.id } })" />
</template>

<script setup lang="ts">
import type { SearchResultResource } from '..'

const { item } = defineProps<{ item: SearchResultResource }>()
const router = useRouter()
</script>

<template>
  <ContractVersionItem
    :item="item.contract!"
    @click="router.push({
      name: 'clients-id',
      params: { id: item.contract!.contract.client.id },
      hash: '#contracts',
    })"
  />
</template>

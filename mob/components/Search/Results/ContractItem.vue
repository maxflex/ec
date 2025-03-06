<script setup lang="ts">
import type { SearchResultResource } from '..'

const { item } = defineProps<{
  item: SearchResultResource
}>()

const contract = item.contract!
</script>

<template>
  <RouterLink :to="{ name: 'clients-id', params: { id: contract.client_id } }" class="search-contract-item">
    <div style="width: 200px">
      договор №{{ item.id }}
    </div>
    <div style="width: 200px">
      {{ YearLabel[contract.year] }}
    </div>
    <div style="width: 130px">
      {{ CompanyLabel[contract.company] }}
    </div>
    <div>
      <ContractVersionDirections :item="contract" />
    </div>
  </RouterLink>
</template>

<style lang="scss">
.search-contract-item {
  & > div {
    padding: 16px 0;
  }
}
</style>

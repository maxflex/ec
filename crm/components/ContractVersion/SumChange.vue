<script setup lang="ts">
const { item } = defineProps<{
  item: ContractVersionListResource
}>()
</script>

<template>
  <span v-if="item.sum_change > 0" class="text-success">
    +{{ formatPrice(item.sum_change) }} руб.
  </span>
  <span v-if="item.sum_change < 0" class="text-error">
    {{ formatPrice(item.sum_change) }} руб.
  </span>
</template>

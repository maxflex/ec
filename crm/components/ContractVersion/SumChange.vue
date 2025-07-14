<script setup lang="ts">
const { item } = defineProps<{
  item: ContractVersionListResource
}>()
</script>

<template>
  <span v-if="item.sum_change > 0" class="very-small text-success">
    +{{ formatPrice(item.sum_change) }}
  </span>
  <span v-if="item.sum_change < 0" class="very-small text-error">
    {{ formatPrice(item.sum_change) }}
  </span>
</template>

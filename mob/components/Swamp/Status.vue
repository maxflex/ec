<script setup lang="ts">
const { item } = defineProps<{
  item: SwampListResource
}>()
</script>

<template>
  <span v-if="item.total_price === item.total_price_passed" class="text-success">
    исполнено
  </span>
  <span v-else-if="item.total_price_passed > item.total_price" class="text-error">
    избыток по услугам
  </span>
  <span v-else-if="item.group_id" class="text-gray">
    исполняется
  </span>
  <span v-else class="text-orange">
    к исполнению
  </span>
</template>

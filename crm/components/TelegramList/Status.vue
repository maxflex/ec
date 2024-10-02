<script setup lang="ts">
const { item } = defineProps<{
  item: TelegramListResource
}>()
</script>

<template>
  <span v-if="item.is_sent" class="text-success">
    отправлено
  </span>
  <span v-else-if="item.scheduled_at">
    запланирована на {{ formatDateTime(item.scheduled_at) }}
  </span>
  <span v-else>
    отправляется...
  </span>
</template>

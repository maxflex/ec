<script setup lang="ts">
import type { TelegramListResource } from '.'

const { item } = defineProps<{
  item: TelegramListResource
}>()
</script>

<template>
  <span v-if="item.status" :class="{ 'text-success': item.status === 'sent' }">
    {{ TelegramListStatusLabel[item.status] }}
  </span>
</template>

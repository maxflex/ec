<script setup lang="ts">
import type { RequestListResource } from '../Request'

const { item, request } = defineProps<{
  item: PhoneResource
  request?: RequestListResource
}>()
function getClass(): string {
  if (request && !request.is_verified) {
    return 'text-gray'
  }
  if (item.is_telegram_disabled) {
    return 'text-error'
  }
  if (item.telegram_id) {
    return 'text-secondary'
  }
  return 'text-black'
}
</script>

<template>
  <span :class="getClass()">
    {{ formatPhone(item.number) }}
  </span>
</template>

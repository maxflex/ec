<script setup lang="ts">
import type { TelegramListResource } from '.'

const { item } = defineProps<{
  item: TelegramListResource
}>()
</script>

<template>
  <span v-if="item.send_to.length === Object.keys(SendToLabel).length">
    всем
  </span>
  <span v-else>
    {{ item.send_to.map(e => SendToAltLabel[e]).join(' и ') }}
  </span>
</template>

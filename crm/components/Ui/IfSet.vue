<script setup lang="ts">
const { value } = defineProps<{
  value: any
}>()
</script>

<template>
  <slot v-if="value">
    {{ value }}
  </slot>
  <span v-else class="text-gray">
    <slot name="empty" />
  </span>
</template>

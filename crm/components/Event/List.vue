<script setup lang="ts">
const { items } = defineProps<{
  items: EventResource[]
}>()
</script>

<template>
  <div>
    {{ items }}
  </div>
</template>

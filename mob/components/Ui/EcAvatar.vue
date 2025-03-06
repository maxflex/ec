<script setup lang="ts">
const { size } = defineProps<{
  size?: number
}>()
</script>

<template>
  <v-avatar
    color="brown"
    :size="size || 100"
  >
    <span class="text-h6">EC</span>
  </v-avatar>
</template>

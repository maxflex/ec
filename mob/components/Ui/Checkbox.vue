<script setup lang="ts">
const { value } = defineProps<{
  value: boolean
}>()
</script>

<template>
  <v-icon
    v-if="value"
    color="secondary"
    icon="$checkboxOn"
  />
  <v-icon
    v-else
    icon="$checkboxOff"
    class="opacity-6"
  />
</template>

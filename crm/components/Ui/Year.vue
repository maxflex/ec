<script setup lang="ts">
const { year } = defineProps<{
  year: any
}>()
</script>

<template>{{ year }}–{{ year + 1 }} уч. г.</template>

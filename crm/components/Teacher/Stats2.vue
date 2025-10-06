<script setup lang="ts">
const { teacherId } = defineProps<{ teacherId: number }>()
</script>

<template>
  <div>
    <h1>stats 2.0</h1>
    <h2>{{ teacherId }}</h2>
  </div>
</template>

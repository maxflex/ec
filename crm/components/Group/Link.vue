<script setup lang="ts">
const { item } = defineProps<{ item: {
  id: number
  letter: number | null
} }>()
</script>

<template>
  <NuxtLink :to="{ name: 'groups-id', params: { id: item.id } }">
    ГР-{{ item.id }}{{ item.letter ? `-${item.letter}` : '' }}
  </NuxtLink>
</template>

<script setup lang="ts">
const { item } = defineProps<{ item: {
  id: number
  level: number | null
} }>()
</script>

<template>
  <NuxtLink :to="{ name: 'groups-id', params: { id: item.id } }">
    ГР-{{ item.id }}{{ item.level ? `-${item.level}` : '' }}
  </NuxtLink>
</template>

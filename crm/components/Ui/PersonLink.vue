<script setup lang="ts">
const { item, type } = defineProps<{
  type: 'teachers' | 'clients'
  item: PersonResource
}>()
</script>

<template>
  <RouterLink v-if="type === 'teachers'" :to="{ name: 'teachers-id', params: { id: item.id } }">
    {{ formatName(item, 'full') }}
  </RouterLink>
  <RouterLink v-else :to="{ name: 'clients-id', params: { id: item.id } }">
    {{ formatName(item) }}
  </RouterLink>
</template>

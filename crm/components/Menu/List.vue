<script setup lang="ts">
const { items } = defineProps<{
  items: Menu
}>()
</script>

<template>
  <v-list-item
    v-for="{ title, to, icon } in items"
    :key="title"
    :to="to"
  >
    <template #prepend>
      <v-icon :icon="icon" />
    </template>
    {{ title }}
  </v-list-item>
</template>

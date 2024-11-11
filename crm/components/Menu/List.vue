<script setup lang="ts">
const { items } = defineProps<{
  items: Menu
}>()
</script>

<template>
  <v-list-item
    v-for="{ title, to, icon, count } in items"
    :key="title"
    :to="to"
  >
    <template #prepend>
      <v-icon :icon="icon" />
    </template>
    {{ title }}
    <template v-if="count && menuCounts[count]" #append>
      <UiCircle color="error" class="pr-1" />
    </template>
  </v-list-item>
</template>

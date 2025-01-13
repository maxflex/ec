<script setup lang="ts">
const { item } = defineProps<{
  item: MenuItem
}>()
</script>

<template>
  <v-list-item :to="item.to">
    <template v-if="item.icon" #prepend>
      <v-icon :icon="item.icon" />
    </template>
    {{ item.title }}
    <template v-if="item.count && menuCounts[item.count]" #append>
      <UiCircle color="error" class="pr-1" />
    </template>
  </v-list-item>
</template>

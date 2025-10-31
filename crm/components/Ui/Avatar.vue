<script setup lang="ts">
const { item, size = 100 } = defineProps<{
  item: PersonWithPhotoResource
  size?: number
}>()

const src = computed(() => item.photo_url ?? `/img/avatar/male.png`)
</script>

<template>
  <v-avatar :size="size">
    <v-img eager :src="src" />
  </v-avatar>
</template>

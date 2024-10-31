<script setup lang="ts">
const { user, size = 60 } = defineProps<{
  user: PersonResource
  size?: number
}>()
const id = (Math.abs(user.id) % 25) + 1
</script>

<template>
  <v-avatar :size="size">
    <img :src="`/avatars/avatar-${id}.svg`">
  </v-avatar>
</template>

<script setup lang="ts">
const { user, size } = withDefaults(
  defineProps<{
    user: PersonResource
    size?: number
  }>(),
  {
    size: 60,
  },
)
const id = (user.id % 25) + 1
</script>

<template>
  <v-avatar :size="size">
    <v-img :src="`/avatars/avatar-${id}.svg`" />
  </v-avatar>
</template>

<script setup lang="ts">
const { item, size = 100 } = defineProps<{
  item: PersonWithPhotoResource
  size?: number
}>()

const src = computed(() => item.photo_url ?? `/img/avatar/male.png`)
</script>

<template>
  <v-avatar :size="size">
    <v-img :src="src" />
  </v-avatar>
  <!-- <div
    class="ui-avatar"
    :style="{
      height: `${size}px`,
      width: `${size}px`,
    }"
  >
    <img :src="src">
  </div> -->
</template>

<!-- <style lang="scss">
.ui-avatar {
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 50%;
  img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensure the image covers the container without distortion */
    border-radius: 50%; /* Make the image itself a circle */
  }
}
</style> -->

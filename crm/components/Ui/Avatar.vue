<script setup lang="ts">
import { getGender } from 'lvovich'

const { item, size } = withDefaults(
  defineProps<{
    item: PersonWithPhotoResource
    size?: number
  }>(),
  {
    size: 100,
  },
)

const src = computed(() => {
  if (item.photo_url) {
    return item.photo_url
  }
  const gender = getGender({
    first: item.first_name,
    last: item.last_name,
    middle: item.middle_name,
  })
  return `/img/avatar/${gender === 'female' ? 'female' : 'male'}.png`
})
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

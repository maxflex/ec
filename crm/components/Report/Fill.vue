<script setup lang="ts">
const model = defineModel<number>({ required: true })

const color = computed(() => {
  if (model.value > 80) {
    return 'success'
  }
  if (model.value > 50) {
    return 'orange'
  }
  return 'error'
})
</script>

<template>
  <v-progress-linear
    bg-color="#92aed9"
    :color="color"
    height="12"
    max="100"
    min="0"
    :model-value="model"
    rounded
  />
</template>

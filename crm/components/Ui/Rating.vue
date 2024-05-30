<script setup lang="ts">
const model = defineModel<number>({ default: () => 0 })
</script>

<template>
  <div class="rating">
    <v-icon
      v-for="i in 5"
      :key="i"
      :icon="model >= i ? '$ratingFull' : '$ratingEmpty'"
      color="orange"
    />
  </div>
</template>

<style lang="scss">
.rating {
  display: flex;
  .v-icon {
    font-size: 20px;
  }
}
</style>

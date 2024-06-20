<script setup lang="ts">
const { loading } = defineProps<{ loading: boolean }>()
</script>

<template>
  <v-fade-transition>
    <div v-if="loading" class="white-loader" />
  </v-fade-transition>
</template>

<style lang="scss">
.white-loader {
  position: absolute;
  background: rgba(white, 0.5);
  height: 100%;
  width: 100%;
  left: 0;
  top: 0;
  z-index: 1;
}
</style>

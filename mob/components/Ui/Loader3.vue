<script setup lang="ts">
const { loading } = defineProps<{
  loading: boolean
}>()
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="loading" class="loader-3" />
  </v-fade-transition>
</template>

<style lang="scss">
.loader-3 {
  position: absolute;
  background: rgba(white, 0.5);
  width: 100%;
  height: 100%;
  z-index: 99;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>

<script setup lang="ts">
const { paginator } = defineProps<{
  paginator?: Paginator
  loading?: boolean
}>()
</script>

<template>
  <v-fade-transition>
    <UiLoaderr
      v-if="paginator ? paginator.loading && paginator.page < 2 : loading"
      class="loader"
    />
  </v-fade-transition>
</template>

<style lang="scss">
.loader {
  position: absolute;
  background: white;
  width: calc(100% - var(--v-layout-left));
  height: 90vh;
  z-index: 99;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>

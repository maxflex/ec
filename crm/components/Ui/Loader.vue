<script setup lang="ts">
const { paginator } = defineProps<{
  paginator?: Paginator
  loading?: boolean
}>()
</script>

<template>
  <v-fade-transition>
    <div
      class="loader"
      v-if="paginator ? paginator.loading && paginator.page < 2 : loading"
    >
      <v-progress-circular :size="50" indeterminate />
    </div>
  </v-fade-transition>
</template>

<style lang="scss">
.loader {
  position: absolute;
  background: white;
  width: calc(100% - var(--v-layout-left));
  height: 100%;
  z-index: 99;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>

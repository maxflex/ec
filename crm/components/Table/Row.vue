<script setup lang="ts">
const { background } = defineProps<{
  /**
   * Легкая подсветка фона
   */
  background?: 'blue' | 'orange' | 'gray'
}>()
</script>

<template>
  <div class="table-row" :class="[background ? `table-row--${background}` : '']">
    <slot></slot>
  </div>
</template>

<style lang="scss">
.table-row {
  &--blue {
    background: rgba(var(--v-theme-secondary), 0.05);
  }
  &--orange {
    background: rgba(var(--v-theme-primary), 0.3);
  }
  &--gray {
    background: rgb(var(--v-theme-bg));
  }
}
</style>

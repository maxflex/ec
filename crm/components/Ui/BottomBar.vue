<script setup lang="ts">
const { color } = defineProps<{
  color?: GlobalMessageColor
}>()
const model = defineModel<boolean>({
  default: true,
})
</script>

<template>
  <v-slide-y-reverse-transition>
    <div v-if="model" class="bottom-bar" :class="color && `bottom-bar--${color}`">
      <slot />
    </div>
  </v-slide-y-reverse-transition>
</template>

<style lang="scss">
.bottom-bar {
  position: fixed !important;
  bottom: 0;
  left: var(--menuWidth);
  padding: 0 20px;
  height: 52px;
  z-index: 100;
  background: rgb(var(--v-theme-bg));
  width: calc(100vw - var(--menuWidth));
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: thin solid rgb(var(--v-theme-border));
  // border-top: 2px solid rgb(var(--v-theme-gray));
  & > div {
    display: flex;
    align-items: center;
    gap: 8px;

    &:first-child {
      .v-btn {
        margin-left: -6px;
        margin-top: 1px;
        opacity: 0.6;
      }
    }
  }
  &--error {
    background: #ffebee;
    color: black;
    z-index: 9999;
    border-color: #ffcdd2;
  }
  &--success {
    border-color: #c8e6c9;
    background: #e8f5e9;
    color: black;
    z-index: 9999;
  }
}
</style>

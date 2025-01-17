<script setup lang="ts">
const { color } = defineProps<{
  color: GlobalMessageColor
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
  left: 256px;
  padding: 0 20px;
  height: 52px;
  z-index: 3;
  background: rgb(var(--v-theme-bg));
  width: calc(100vw - 256px);
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
    //border-color: rgb(var(--v-theme-error));
  }
  &--success {
    background: rgba(var(--v-theme-success), 0.1);
    color: rgb(var(--v-theme-success));
    z-index: 999;
  }
}
</style>

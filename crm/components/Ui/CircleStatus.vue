<template>
  <div class="circle-status" v-bind="$attrs">
    <div class="circle-status__circle" />
    <slot />
  </div>
</template>

<style lang="scss">
.circle-status {
  display: flex;
  align-items: center;
  gap: 4px;
  color: currentColor;
  &__circle {
    --size: 8px;
    height: var(--size);
    width: var(--size);
    border-radius: 50%;
    background-color: currentColor;
    top: 1px;
    position: relative;
  }
}
</style>

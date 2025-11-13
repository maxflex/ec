<template>
  <div class="fullwidth">
    <slot></slot>
  </div>
</template>

<style lang="scss">
.fullwidth {
  margin-left: calc(var(--padding) * -1);
  width: calc(100% + var(--padding) * 2);
  min-width: calc(100% + var(--padding) * 2);
}
</style>

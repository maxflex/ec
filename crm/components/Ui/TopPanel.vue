<template>
  <div class="top-panel">
    <slot />
  </div>
</template>

<style lang="scss">
.top-panel {
  display: flex;
  background: rgb(var(--v-theme-bg));
  border-bottom: 1px solid #e0e0e0;
  padding: 10px 20px;
}
</style>

<template>
  <div class="lesson-status-4">
    <div class="lesson-status-4__circle" />
    внеплановое
  </div>
</template>

<style lang="scss">
.lesson-status-4 {
  display: flex;
  align-items: center;
  gap: 4px;
  --color: violet;
  color: var(--color);
  &__circle {
    --size: 8px;
    height: var(--size);
    width: var(--size);
    border-radius: 50%;
    background-color: var(--color);
    top: 1px;
    position: relative;
  }
}
</style>

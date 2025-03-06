<template>
  <div class="filters" :class="$props.class">
    <div class="filters__inputs">
      <slot />
    </div>
    <slot name="buttons" />
  </div>
</template>

<style lang="scss">
.filters {
  background: rgb(var(--v-theme-bg));
  padding: 0 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  // justify-content: flex-end;
  border-bottom: 1px solid rgb(var(--v-theme-border));
  gap: 40px;
  --height: 50px;
  min-height: var(--height);
  max-height: var(--height);
  height: var(--height);
  &__inputs {
    display: flex;
    align-items: center;
    gap: 10px;
    & > div {
      width: 250px;
    }
  }
}
</style>

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
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  // justify-content: flex-end;
  border-bottom: 1px solid rgb(var(--v-theme-border));
  gap: 40px;
  $height: 81px;
  min-height: $height;
  max-height: $height;
  height: $height;
  &__inputs {
    display: flex;
    align-items: center;
    gap: 20px;
    & > div {
      width: 250px;
    }
  }
}
</style>

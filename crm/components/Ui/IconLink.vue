<template>
  <a class="icon-link">
    <slot />
    <v-icon
      :size="16"
      icon="$next"
    />
  </a>
</template>

<style scoped lang="scss">
.icon-link {
  --gap: 1px;
  display: inline-flex;
  cursor: pointer;
  align-items: center;
  gap: var(--gap);
  transition: gap 0.2s cubic-bezier(0.2, 0, 0.4, 0.9);
  user-select: none;
  .v-icon {
    top: 1px;
    position: relative;
  }
  &:hover {
    gap: calc(var(--gap) + 2px);
  }
  &:active {
    gap: var(--gap);
  }
}
</style>

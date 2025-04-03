<script lang="ts" setup>
const { icon = '$next', prepend } = defineProps<{
  icon?: string
  prepend?: boolean
}>()
</script>

<template>
  <a class="icon-link" :class="{ 'icon-link--prepend': prepend }">
    <slot />
    <v-icon :size="16" :icon="icon" />
  </a>
</template>

<style lang="scss">
.icon-link {
  --gap: 1px;
  display: inline-flex;
  cursor: pointer;
  align-items: center;
  gap: var(--gap);
  transition: gap 0.2s cubic-bezier(0.2, 0, 0.4, 0.9);
  user-select: none;
  font-size: 14px;
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
  &--prepend {
    flex-direction: row-reverse;
    gap: 4px !important;
  }
}
</style>

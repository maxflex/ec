<script setup lang="ts">
import type { CSSProperties } from 'vue'

const { sticky } = defineProps<{ sticky?: boolean }>()
const el = ref()

const style = computed<CSSProperties>(() => {
  if (!sticky || !el.value) {
    return {}
  }
  return {
    position: 'sticky',
    top: `${el.value.offsetTop}px`,
    zIndex: 100,
  }
})
</script>

<template>
  <div ref="el" class="filters" :class="$props.class" :style="style">
    <div class="filters__inputs">
      <slot />
    </div>
    <slot name="buttons" />
  </div>
</template>

<style lang="scss">
.filters {
  position: relative;
  background: rgb(var(--v-theme-bg));
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  // justify-content: flex-end;
  border-bottom: 1px solid rgb(var(--v-theme-border));
  gap: 40px;
  --height: 81px;
  min-height: var(--height);
  max-height: var(--height);
  height: var(--height);
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

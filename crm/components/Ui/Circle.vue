<script lang="ts" setup>
import type { StyleValue } from 'vue'

const { size = 8, color } = defineProps<{
  size?: number
  color: string
}>()

let className: string
let style: StyleValue

if (color.startsWith('#')) {
  style = { color }
}
else {
  className = `text-${color}`
}
</script>

<template>
  <div class="circle" :style="style" :class="className">
    <div
      class="circle__circle" :style="{
        height: `${size}px`,
        width: `${size}px`,
      }"
    />
    <slot />
  </div>
</template>

<style lang="scss">
.circle {
  display: flex;
  align-items: center;
  gap: 4px;
  color: currentColor;
  &__circle {
    border-radius: 50%;
    background-color: currentColor;
    top: 1px;
    position: relative;
  }
}
</style>

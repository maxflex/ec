<script setup lang="ts">
import type { CSSProperties } from 'vue'

const { width } = defineProps<{
  width?: number
}>()

const style: CSSProperties = width
  ? {
      width: `${width}px`,
    }
  : {
      flex: 1,
    }
</script>

<template>
  <div class="table-col" :style="style" :class="{ 'text-truncate': !width }">
    <slot></slot>
  </div>
</template>

<style lang="scss">
.table-col {
  position: relative;
}
</style>

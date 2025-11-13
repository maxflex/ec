<script setup lang="ts">
const { width } = defineProps<{
  width?: number
}>()

const style = width
  ? {
      width: `${width}px`,
    }
  : {
      flex: 1,
    }
</script>

<template>
  <div class="table-col" :style="style">
    <slot></slot>
  </div>
</template>

<style lang="scss">
.table-col {
  position: relative;
}
</style>

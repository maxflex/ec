<script setup lang="ts">
defineOptions({ inheritAttrs: false })
</script>

<template>
  <div class="table-actions" v-bind="$attrs">
    <slot />
  </div>
</template>

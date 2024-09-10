<script setup lang="ts">
const { status } = defineProps<{
  status: LessonStatus
}>()
</script>

<template>
  <div :class="`lesson-status lesson-status--${status}`" />
</template>

<style lang="scss">
.lesson-status {
  --size: 7px;
  --bg: red;
  height: var(--size);
  width: var(--size);
  border-radius: 50%;
  background-color: var(--bg);
  &--planned {
    --bg: #949db1;
  }
  &--conducted {
    --bg: #00a272;
  }
  &--cancelled {
    --bg: #eb4432;
  }
}
</style>

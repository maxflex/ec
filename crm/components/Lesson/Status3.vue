<script setup lang="ts">
const { status } = defineProps<{
  status: LessonStatus
}>()
</script>

<template>
  <div :class="`lesson-status-3 lesson-status-3--${status}`">
    <div class="lesson-status-3__circle" />
    {{ LessonStatusLabel[status] }}
  </div>
</template>

<style lang="scss">
.lesson-status-3 {
  display: flex;
  align-items: center;
  gap: 4px;
  --color: red;
  color: var(--color);
  &--planned {
    --color: #949db1;
  }
  &--conducted {
    --color: #00a272;
  }
  &--cancelled {
    --color: #eb4432;
  }
  &__circle {
    --size: 8px;
    height: var(--size);
    width: var(--size);
    border-radius: 50%;
    background-color: var(--color);
    top: 1px;
    position: relative;
  }
}
</style>

<script setup lang="ts">
const { score, float } = defineProps<{
  score: number | null
  float?: boolean
}>()
</script>

<template>
  <div
    v-if="score"
    v-bind="$attrs"
    class="scholarship-score"
    :class="`scholarship-score--${Number.parseInt(score)}`"
  >
    {{ float ? parseFloat(score.toString()).toFixed(1) : score }}
  </div>
</template>

<style lang="scss">
.scholarship-score {
  $size: 34px;
  height: $size !important;
  width: $size !important;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: default;
  border: 1px solid transparent;
  font-weight: 500;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.5, 1);
  color: white;
  background: #ff7575;
  user-select: none;
  font-size: 13px;
  &--5,
  &--6,
  &--7 {
    background: #f9be04 !important;
  }
  &--8,
  &--9,
  &--10 {
    background: #77ce1e !important;
  }
}
</style>

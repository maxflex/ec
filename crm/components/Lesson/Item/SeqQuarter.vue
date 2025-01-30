<script setup lang="ts">
const { item, hideQuarter } = defineProps<{
  item: LessonListResource
  hideQuarter?: boolean
}>()
</script>

<template>
  <div v-if="item.status !== 'cancelled'" class="lesson-item__seq-quarter">
    {{ item.seq }}
    <span v-if="item.quarter && !hideQuarter">
      / {{ item.quarter[1] }}
    </span>
  </div>
</template>

<style lang="scss">
.lesson-item__seq-quarter {
  color: rgb(var(--v-theme-gray));
  opacity: 0.5;
  text-align: right;
  padding-right: 4px;
}
</style>

<script setup lang="ts">
const { item, size = 10 } = defineProps<{
  item: LessonListResource
  size?: number
}>()
</script>

<template>
  <div class="lesson-status-circles">
    <LessonStatus :item="item" :size="size" />
    <UiCircle v-if="item.is_unplanned" color="purple" :size="size" />
    <UiCircle v-if="item.is_free" color="primary" :size="size" />
  </div>
</template>

<style scoped lang="scss">
.lesson-status-circles {
  display: flex;
  align-items: center;
  gap: 4px;
}
</style>

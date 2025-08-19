<script setup lang="ts">
const { item, showUnplanned, showFree } = defineProps<{
  item: LessonListResource
  showUnplanned?: boolean
  showFree?: boolean
}>()
const { status } = item
</script>

<template>
  <div
    :class="{
      'text-error': status === 'cancelled',
      'text-gray': status === 'planned',
    }"
  >
    {{ LessonStatusLabel[status] }}
  </div>
  <div v-if="showUnplanned && item.is_unplanned" class="text-purple">
    внеплановое
  </div>
  <div v-if="showFree && item.is_free" class="text-orange">
    бесплатное
  </div>
</template>

<style lang="scss">
.lesson-item__status {
  width: 110px;
  line-height: 18px;
  margin-top: 3px;
}
</style>

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
      'text-success': status === 'conducted',
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
    бесплатное для детей
  </div>
</template>

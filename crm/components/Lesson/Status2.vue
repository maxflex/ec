<script setup lang="ts">
const { item } = defineProps<{
  item: LessonListResource
}>()
</script>

<template>
  <span
    :class="{
      'text-error': item.status === 'cancelled',
      'text-gray': item.status === 'planned',
    }"
  >
    {{ LessonStatusLabel[item.status] }}
  </span>
</template>

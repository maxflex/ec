<script setup lang="ts">
const { item } = defineProps<{
  item: LessonListResource
}>()
</script>

<template>
  <span
    :class="{
      'text-gray': item.status !== 'conducted',
    }"
  >
    {{ LessonStatusLabel[item.status] }}
  </span>
</template>

<script setup lang="ts">
const { item, size = 10, showLabel } = defineProps<{
  item: LessonListResource
  showLabel?: boolean
  size?: number
}>()

const statusColor = (function () {
  if (item.is_need_conduct) {
    return 'error'
  }
  switch (item.status) {
    case 'planned':
      return 'gray'
    case 'conducted':
      return 'success'
    case 'cancelled':
      return 'error'
  }
})()
</script>

<template>
  <UiCircle :color="statusColor" :size="size">
    <template v-if="showLabel" #default>
      {{ LessonStatusLabel[item.status] }}
    </template>
  </UiCircle>
</template>

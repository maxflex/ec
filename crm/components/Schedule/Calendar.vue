<script setup lang="ts">
import type { CalendarDialog } from '#components'

const { lessons } = defineProps<{ lessons: LessonListResource[] }>()
const model = ref<string[]>([])

const calendarDialog = ref<InstanceType<typeof CalendarDialog>>()

const lessonsByDate = computed<Record<string, Record<LessonStatus, number>>>(() => {
  const result: Record<string, Record<LessonStatus, number>> = {}
  for (const lesson of lessons) {
    if (!(lesson.date in result)) {
      result[lesson.date] = {
        planned: 0,
        cancelled: 0,
        conducted: 0,
      }
    }
    result[lesson.date][lesson.status]++
  }

  return result
})
</script>

<template>
  <v-btn icon="$calendar" :size="48" color="primary" v-bind="$attrs" @click="calendarDialog?.open()" />
  <CalendarDialog ref="calendarDialog" v-model="model" :lessons="lessonsByDate" />
</template>

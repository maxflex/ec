<script setup lang="ts">
import { format } from 'date-fns'

const { date } = defineProps<{
  date: string | undefined | null
}>()

const hasFirstLessonDate = !!date

const dateFormatted = hasFirstLessonDate ? format(date, 'dd.MM') : ''
</script>

<template>
  <UiIfSet :value="hasFirstLessonDate">
    <template #empty>
      даты нет
    </template>
    {{ dateFormatted }}
  </UiIfSet>
</template>

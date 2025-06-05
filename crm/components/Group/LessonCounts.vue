<script setup lang="ts">
import { mdiArrowRightThin } from '@mdi/js'
import { format } from 'date-fns'

const { item } = defineProps<{
  item: GroupListResource
  // складывать бесплатные занятия
  sumFree?: boolean
}>()

const firstLessonDate = item.first_lesson_date
  ? format(item.first_lesson_date, 'dd.MM')
  : ''
</script>

<template>
  <span v-if="item.lessons.conducted === 0 && item.first_lesson_date">
    <span class="text-orange">
      {{ firstLessonDate }}
    </span>
    <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
    {{ item.lessons.planned }}
    <template v-if="item.lessons_planned !== item.lessons.planned">
      ({{ item.lessons_planned }})
    </template>
  </span>
  <span v-else>
    {{ item.lessons.conducted + (sumFree ? item.lessons.conducted_free : 0) }}
    <span v-if="item.lessons.conducted_free && !sumFree" class="text-orange">
      + {{ item.lessons.conducted_free }}
    </span>
    <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
    {{ item.lessons.planned + (sumFree ? item.lessons.planned_free : 0) }}
    <span v-if="item.lessons.planned_free && !sumFree" class="text-orange">
      + {{ item.lessons.planned_free }}
    </span>
    <template v-if="item.lessons_planned !== item.lessons.planned">
      ({{ item.lessons_planned }})
    </template>
  </span>
</template>

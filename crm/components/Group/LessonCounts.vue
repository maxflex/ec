<script setup lang="ts">
import { mdiArrowRightThin } from '@mdi/js'

const { item } = defineProps<{
  item: GroupListResource | GroupResource
  // складывать бесплатные занятия
  sumFree?: boolean
}>()
</script>

<template>
  <div>
    {{ item.lesson_counts.conducted + (sumFree ? item.lesson_counts.conducted_free : 0) }}
    <!-- <div v-if="item.lesson_counts.conducted_free && !sumFree" class="text-orange">
      + {{ item.lesson_counts.conducted_free }}
    </span> -->
    <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
    {{ item.lesson_counts.planned + (sumFree ? item.lesson_counts.planned_free : 0) }}
    <!-- <span v-if="item.lesson_counts.planned_free && !sumFree" class="text-orange">
      + {{ item.lesson_counts.planned_free }}
    </span> -->
    <template v-if="item.lessons_planned !== item.lesson_counts.planned">
      ({{ item.lessons_planned }})
    </template>
  </div>
  <div v-if="item.lesson_counts.conducted_free || item.lesson_counts.planned_free" class="text-orange">
    {{ item.lesson_counts.conducted_free }}
    <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
    {{ item.lesson_counts.planned_free }}
  </div>
</template>

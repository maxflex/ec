<script setup lang="ts">
const { item } = defineProps<{
  item: {
    draft_students_count: number
    client_groups_count?: number
    students_count?: number
    status?: LessonStatus
  }
}>()

const studentsCount = 'students_count' in item
  ? item.students_count
  : item.client_groups_count
</script>

<template>
  <span :class="{ 'text-gray': !studentsCount }">
    {{ studentsCount }}
    <template v-if="item.draft_students_count && item.status !== 'conducted'">
      <span v-if="item.draft_students_count > 0" class="text-deepOrange">
        + {{ item.draft_students_count }}
      </span>
      <span v-else class="text-deepOrange">
        - {{ Math.abs(item.draft_students_count) }}
      </span>
    </template>
    уч.
  </span>
</template>

<script setup lang="ts">
const { item } = defineProps<{
  item: {
    client_groups_count: number
    students_count: number
    draft_students_count: number
  }
}>()

const studentsCount = 'students_count' in item
  ? item.students_count
  : item.client_groups_count
</script>

<template>
  <span :class="{ 'text-gray': !studentsCount }">
    {{ studentsCount || '0' }}
    <template v-if="item.draft_students_count">
      <span v-if="item.draft_students_count > 0" class="text-gray">
        + {{ item.draft_students_count }}
      </span>
      <span v-else class="text-gray">
        - {{ Math.abs(item.draft_students_count) }}
      </span>
    </template>
    уч.
  </span>
</template>

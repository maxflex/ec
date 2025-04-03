<script setup lang="ts">
const { item } = defineProps<{
  item: LessonListResource
}>()
</script>

<template>
  <div class="lesson-item lesson-item__lesson">
    <div class="lesson-item__row">
      <div class="font-weight-medium">
        {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
      </div>
      <div v-if="item.cabinet">
        {{ CabinetAllLabel[item.cabinet] }}
      </div>
    </div>
    <div class="lesson-item__row">
      <div v-if="item.teacher" class="text-truncate">
        {{ formatNameInitials(item.teacher) }}
      </div>
      <div>
        {{ ProgramShortLabel[item.group.program] }}
      </div>
    </div>
    <div class="lesson-item__row">
      <div>
        {{ item.group.zoom.id }}
      </div>
      <div>
        {{ item.group.zoom.password }}
      </div>
    </div>
    <div class="lesson-item__row">
      <div
        :class="{
          'text-success': item.status === 'conducted',
          'text-error': item.status === 'cancelled',
          'text-gray': item.status === 'planned',
        }"
      >
        {{ LessonStatusLabel[item.status] }}
      </div>
      <div v-if="item.is_unplanned" class="text-purple">
        внеплановое
      </div>
    </div>
  </div>
</template>

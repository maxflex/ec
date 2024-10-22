<script setup lang="ts">
const { items } = defineProps<{
  items: ReportClientLessonResource[]
}>()
</script>

<template>
  <div v-for="item in items" :key="item.id">
    {{ formatDate(item.lesson.date) }} –
    <span :class="{ 'text-error': item.status === 'absent' }">
      {{ ClientLessonStatusLabel[item.status] }}
    </span>
    <template v-if="item.status !== 'absent'">
      {{ item.is_remote ? ' удалённо' : ' очно' }}
    </template>
    <template v-if="item.status === 'late'">
      на {{ item.minutes_late }} мин.
    </template>
    <template v-if="item.lesson.topic">
      ({{ item.lesson.topic }})
    </template>
  </div>
</template>

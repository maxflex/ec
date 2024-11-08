<script setup lang="ts">
const { items } = defineProps<{
  items: ReportClientLessonResource[]
}>()
</script>

<template>
  <div class="report-client-lessons">
    <div v-for="item in items" :key="item.id">
      <div>
        <span class="font-weight-medium">
          {{ formatDate(item.lesson.date) }}
        </span>
        <span> – </span>
        <span :class="{ 'text-error': item.status === 'absent' }">
          {{ ClientLessonStatusLabel[item.status] }}
        </span>
        <template v-if="item.minutes_late">
          на {{ item.minutes_late }} мин.
        </template>
      </div>
      <div v-if="item.lesson.topic">
        {{ item.lesson.topic }}
      </div>
      <div v-if="item.scores" class="grades__scores">
        <div v-for="(score, i) in item.scores" :key="i">
          <span :class="`score score--${score.score}`">
            {{ score.score }}
          </span>
          <div>
            {{ score.comment }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.report-client-lessons {
  display: flex;
  flex-direction: column;
  gap: 30px;
  margin-bottom: 30px;
  &__scores {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 4px;
    & > div {
      display: flex;
      align-items: center;
      gap: 8px;
    }
  }
}
</style>

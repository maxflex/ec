<script setup lang="ts">
const { items } = defineProps<{
  items: JournalResource[]
}>()
</script>

<template>
  <div class="journal">
    <div v-for="item in items" :key="item.id">
      <div class="journal__columns journal__date">
        <div>
          {{ formatDateMob(item.lesson.date) }}
          <br />
          {{ formatTime(item.lesson.time) }} – {{ formatTime(item.lesson.time_end) }}
        </div>
        <div>
          <UiPerson :item="item.lesson.teacher" />
          <div>
            {{ ProgramShortLabel[item.program] }}
          </div>
        </div>
      </div>

      <div v-if="!item.status" class="text-gray">
        до начала обучения
      </div>
      <div v-else :class="{ 'text-error': item.status === 'absent' }">
        {{ ClientLessonStatusLabel[item.status] }}
        <template v-if="item.minutes_late">
          на {{ item.minutes_late }} мин.
        </template>
      </div>

      <div v-if="item.lesson.topic" class="journal__with-header">
        <div>
          Тема:
        </div>
        <div>
          {{ item.lesson.topic }}
        </div>
      </div>
      <div v-if="item.lesson.homework" class="journal__with-header">
        <div>Домашнее задание:</div>
        <div>
          {{ item.lesson.homework }}
        </div>
      </div>
      <div v-if="item.lesson.files.length" class="journal__files">
        <div v-for="f in item.lesson.files" :key="f.url">
          <FileItem :item="f" downloadable show-size />
        </div>
      </div>
      <div v-if="item.scores.length" class="journal__with-header">
        <div>
          Оценки:
        </div>
        <div class="journal__scores">
          <div v-for="(score, i) in item.scores" :key="i">
            <span :class="`text-score text-score--small text-score--${score.score}`">
              {{ score.score }}
            </span>
            – {{ score.comment || 'комментария нет' }}
          </div>
        </div>
      </div>
      <div v-if="item.comment" class="journal__with-header">
        <div>
          Комментарий:
        </div>
        <div>
          {{ item.comment }}
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.journal {
  font-size: 14px;

  & > div {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    &:not(:last-child) {
      border-bottom: 1px solid rgb(var(--v-theme-border));
    }
  }
  &__columns {
    display: flex;
    gap: 20px;
    align-items: center;
  }
  &__date {
    & > div:first-child {
      font-weight: 500;
    }
  }
  &__with-header {
    & > div {
      &:first-child {
        font-weight: 500;
      }
      &:last-child {
        white-space: pre-wrap; // Preserves line breaks & wraps text
        word-wrap: break-word; // Ensures long words don’t overflow
        overflow-wrap: break-word;
      }
    }
  }
  &__files {
    line-height: 16px;
    & > div {
      max-width: 100%;
      .file-item {
        max-width: 100%;
        // &__name {
        //   color: rgb(var(--v-theme-secondary));
        // }
      }
    }
  }
}
</style>

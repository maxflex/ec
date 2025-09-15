<script lang="ts" setup>
import type { CurrentLessonResource } from '.'
import { format } from 'date-fns'
import { Cabinets } from '../Cabinet'

const { item, teacherId } = defineProps<{
  item: CurrentLessonResource
  teacherId?: number
}>()

const isCurrentLessonStarted = item.time <= format(new Date(), 'HH:mm:00')

const hideTeacher = teacherId && teacherId === item.teacher.id
</script>

<template>
  <div class="lesson-current-lesson">
    <span class="live-badge__dot" aria-hidden="true"></span>
    <span v-if="isCurrentLessonStarted">
      Идёт урок
    </span>
    <span v-else>
      Следующий урок
    </span>
    <span>
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </span>
    <UiPerson v-if="!hideTeacher" :item="item.teacher" />
    <span>
      {{ ProgramShortLabel[item.group.program] }}
    </span>
    <span>
      {{ Cabinets[item.cabinet].label }}
    </span>
  </div>
</template>

<style lang="scss">
.lesson-current-lesson {
  white-space: nowrap;
  color: rgb(var(--v-theme-gray));
  display: inline-flex;
  gap: 14px;
  align-items: center;
  // font-size: 14px;

  a:not(:hover) {
    color: rgb(var(--v-theme-gray)) !important;
  }
}

/* LIVE-плашка с пульсирующим кольцом */
.live-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: rgb(var(--v-theme-error)); /* тот же токен, что и в Vuetify */
  /* необязательно, но можно слегка подсветить фон:
  background: color-mix(in oklab, rgb(var(--v-theme-error)) 8%, transparent);
  padding: 2px 8px; border-radius: 999px;
  */

  &__dot {
    $size: 8px;
    width: $size;
    height: $size;
    border-radius: 50%;
    background: currentColor;
    position: relative;
    flex: 0 0 $size;
    display: inline-block;

    /* пульсирующее кольцо */
    &::after {
      content: '';
      position: absolute;
      inset: -6px;
      border-radius: 50%;
      border: 2px solid currentColor;
      opacity: 0.6;
      transform: scale(0.6);
      animation: live-pulse 1.6s ease-out infinite;
      will-change: transform, opacity;
    }
  }
}

.next-badge {
  opacity: 0.9;
} /* остаётся серым/спокойным */

@keyframes live-pulse {
  to {
    opacity: 0;
    transform: scale(1.2);
  }
}

/* уважение к настройкам доступности */
@media (prefers-reduced-motion: reduce) {
  .live-badge__dot::after {
    animation: none;
  }
}
</style>

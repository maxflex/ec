<script setup lang="ts">
import type { TeacherStats, TeacherStatsField, TeacherStatsItem, TeacherStatsMode } from '.'
import { endOfWeek, format, getMonth } from 'date-fns'
import { avgFields, grayFields, labels, percentFields, tooltips } from '.'

const { stats, mode } = defineProps<{
  stats: TeacherStats
  mode: TeacherStatsMode
}>()

function formatField(item: TeacherStatsItem, field: TeacherStatsField): string {
  const value = item[field]
  if (!value) {
    if (field === 'retention_left' && item.retention_share > 0) {
      return '0'
    }
    if (field === 'retention_share' && item.retention_left > 0) {
      return `${Math.round(100 - value)}%`
    }
    return ''
  }
  if (field === 'retention_share') {
    return `${Math.round(100 - value)}%`
  }
  if (avgFields.includes(field)) {
    return `${value.toFixed(1)}`
  }
  if (percentFields.includes(field)) {
    return `${Math.round(value)}%`
  }

  return formatPrice(value).toString()
}

function formatDateMode(d: string) {
  switch (mode) {
    case 'day':
      return formatDateMonth(d)

    case 'week': {
      const end = endOfWeek(d, { weekStartsOn: 1 })
      const month = getMonth(d)
      const endMonth = getMonth(end)
      const monthLabel = MonthLabelShort[month + 1 as Month]
      const endMonthLabel = MonthLabelShort[endMonth + 1 as Month]

      return month === endMonth
        ? `${format(d, 'd')} – ${format(end, 'd')} ${monthLabel}`
        : `${format(d, 'd')} ${monthLabel} – ${format(end, 'd')} ${endMonthLabel}`
    }

    case 'month': {
      const month = getMonth(d)
      return MonthLabel[month + 1 as Month]
    }
  }
}
</script>

<template>
  <div class="table teacher-stats-table">
    <div class="teacher-stats-table__header">
      <div class="text-gray">
        <!-- дата -->
      </div>
      <div v-for="(label, key) in labels" :key="key" :class="`cursor-default teacher-stats-table--${key}`">
        <v-tooltip location="bottom" :max-width="300">
          <template #activator="{ props }">
            <span v-bind="props">
              {{ label }}
            </span>
          </template>
          {{ tooltips[key] }}
        </v-tooltip>
      </div>
    </div>
    <div
      v-for="(item, d, i) in stats.items"
      :key="d"
      class="teacher-stats-table__content"
      :style="i === Object.keys(stats.items).length - 1 ? { borderBottom: 'none' } : {}"
    >
      <div>
        {{ formatDateMode(d) }}
        <span v-if="mode === 'day'" class="text-gray ml-1">
          {{ formatWeekday(d) }}
        </span>
      </div>
      <div v-for="(_, key) in labels" :key="key" :class="`teacher-stats-table--${key}`">
        {{ formatField(item, key) }}
        <span v-if="key in grayFields" class="teacher-stats-table__gray">
          {{ formatField(item, grayFields[key]!) }}
        </span>
      </div>
    </div>
    <div class="teacher-stats-table__footer">
      <div class="text-gray">
        итого
      </div>
      <div v-for="(_, key) in labels" :key="key" :class="`teacher-stats-table--${key}`">
        {{ formatField(stats.totals, key) }}
        <span v-if="key in grayFields" class="teacher-stats-table__gray">
          {{ formatField(stats.totals, grayFields[key]!) }}
        </span>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.teacher-stats-table {
  // width: max-content;
  // min-width: 100%;
  // width: calc(100vw - var(--menuWidth));
  // overflow: scroll;

  // overflow: scroll;
  // max-height: calc(100vh - 81px);

  & > div {
    flex-wrap: nowrap !important;
    width: max-content !important;
    padding: 0 !important;
    min-height: auto !important;

    & > div {
      white-space: nowrap;
      padding: 0;
      height: 57px;
      display: inline-flex;
      align-items: center;
      justify-content: flex-start;

      // date
      &:first-child {
        min-width: 150px;
        position: sticky;
        left: 0;
        padding-left: var(--padding);
        background: white;
        border-right: thin solid rgb(var(--v-theme-border));
        z-index: 0 !important;
      }
    }
  }

  &__header {
    // top: 81px;
    top: 311px;
    line-height: 20px;
  }

  &__footer {
    bottom: 0;
    border-bottom: none !important;
    border-top: thin solid rgb(var(--v-theme-border));
    font-weight: 500;
  }

  &__header,
  &__footer {
    position: sticky !important;
    background: rgb(var(--v-theme-bg));
    z-index: 1;
    & > div:first-child {
      background: rgb(var(--v-theme-bg)) !important;
      font-weight: normal !important;
    }

    &__content {
      &:last-of-type {
        border-bottom: none !important;
      }
    }
  }

  &__gray {
    color: rgb(var(--v-theme-gray));
    margin-left: 6px;
    font-size: 12px;
  }

  &--lessons_conducted_next_day,
  &--client_lessons_online,
  &--retention_left,
  &--comments {
    border-right: thin solid rgb(var(--v-theme-border));
  }

  // ОПОЗДАНИЯ ПРОВОДКИ
  &--lessons_conducted {
    min-width: 70px;
  }
  &--lessons_conducted_next_day {
    min-width: 80px;
  }

  // ПОСЕЩАЕМОСТЬ
  &--client_lessons {
    min-width: 70px;
  }
  &--client_lessons_avg {
    min-width: 80px;
  }
  &--client_lessons_absent {
    min-width: 70px;
  }
  &--client_lessons_late {
    min-width: 80px;
  }
  &--client_lessons_online {
    min-width: 80px;
  }
  &--client_lessons_absent_share {
    min-width: 90px;
  }
  &--client_lessons_late_share {
    min-width: 90px;
  }
  &--client_lessons_online_share {
    min-width: 90px;
  }

  // УДЕРЖАНИЕ АУДИТОРИИ
  &--retention_new {
    min-width: 80px;
  }
  &--retention_left {
    min-width: 90px;
  }

  // ВЕДОМОСТЬ
  &--lessons_with_homework {
    min-width: 60px;
  }
  &--lessons_with_files {
    min-width: 70px;
  }
  &--scores {
    min-width: 70px;
  }
  &--scores_avg {
    min-width: 90px;
  }
  &--scores_comments {
    min-width: 100px;
  }
  &--comments {
    min-width: 80px;
  }

  // ОТЧЁТЫ
  &--reports_published {
    min-width: 80px;
  }
  &--reports_published_no_price {
    min-width: 100px;
  }
  &--reports_fill_avg {
    min-width: 90px;
  }
  &--reports_grade_avg {
    min-width: 100px;
  }
}
</style>

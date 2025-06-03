<script lang="ts" setup>
import {
  differenceInDays,
  differenceInHours,
  differenceInMonths,
  differenceInWeeks,
  differenceInYears,
} from 'date-fns'

const { item } = defineProps<{
  item: {
    last_seen_at: string | null
  }
}>()

const isToday = ref(false)
const lastSeenAt = item.last_seen_at

const dateFormatted = lastSeenAt === null
  ? ''
  : (function () {
      const now = new Date()

      const diffInHours = differenceInHours(now, lastSeenAt)
      const diffInDays = differenceInDays(now, lastSeenAt)
      const diffInWeeks = differenceInWeeks(now, lastSeenAt)
      const diffInMonths = differenceInMonths(now, lastSeenAt)
      const diffInYears = differenceInYears(now, lastSeenAt)

      if (diffInHours <= 24) {
        isToday.value = true
        return 'сегодня'
      }

      if (diffInDays < 14) {
        return `${plural(diffInDays, ['день', 'дня', 'дней'])}`
      }

      if (diffInMonths < 1) {
        return `${diffInWeeks} недели` // 2-4 недели
      }

      if (diffInMonths < 12) {
        return `${plural(diffInMonths, ['месяц', 'месяца', 'месяцев'])}`
      }

      return `${plural(diffInYears, ['год', 'года', 'лет'])}`
    })()
</script>

<template>
  <span
    :class="{ 'text-success': isToday }"
    class="last-seen-at"
  >
    {{ dateFormatted }}
  </span>
</template>

<script setup lang="ts">
import { eachDayOfInterval, endOfMonth, format, getDay, startOfMonth } from 'date-fns'

const year = ref<Year>(2023)
const dayLabels = ['пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс']

const dates = computed(() => {
  // Define the start and end months for the academic year
  const startMonth = 8 // September (0-indexed)
  const endMonth = 4 // May (0-indexed)

  // Define start and end dates for the academic year
  const startDate = startOfMonth(new Date(year.value, startMonth, 1)) // September 1st
  const endDate = endOfMonth(new Date(year.value + 1, endMonth, 31)) // May 31st

  // Generate array of all dates between startDate and endDate
  const allDates = eachDayOfInterval({ start: startDate, end: endDate })

  return allDates.map(d => format(d, 'yyyy-MM-dd'))
})

const offset = computed(() => {
  let day = getDay(dates.value[0])
  day = day === 0 ? 7 : day
  return day - 1
})
</script>

<template>
  <div class="schedule">
    <div class="filters">
      <div class="filters-inputs">
        <v-select
          v-model="year"
          label="Учебный год"
          :items="selectItems(YearLabel)"
          density="comfortable"
        />
      </div>
    </div>
    <div class="schedule-calendar">
      <div v-for="dayLabel in dayLabels" :key="dayLabel" class="text-gray">
        {{ dayLabel }}
      </div>
      <div v-for="i in offset" :key="i" />
      <div v-for="d in dates" :key="d">
        {{ d }}
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.schedule {
  &-calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    &:first-child {
      color: rgb(var(--v-theme-gray));
    }
    & > div {
      padding: 20px;
      border-bottom: 1px solid #e0e0e0;
      border-right: 1px solid #e0e0e0;
    }
  }
}
</style>

<script setup lang="ts">
import { eachDayOfInterval, endOfMonth, format, getDay, startOfMonth } from 'date-fns'
import { groupBy } from 'rambda'
import type { LessonDialog } from '#build/components'

const dayLabels = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']
const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))
const loading = ref(false)
const lessons = ref<LessonListResource[]>([])
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()
const vacations = ref<Record<string, boolean>>({})
const dates = computed(() => {
  // Define the start and end months for the academic year
  const startMonth = 8 // September (0-indexed)
  const endMonth = 4 // May (0-indexed)

  // Define start and end dates for the academic year
  const startDate = startOfMonth(new Date(filters.value.year, startMonth, 1)) // September 1st
  const endDate = endOfMonth(new Date(filters.value.year + 1, endMonth, 31)) // May 31st

  // Generate array of all dates between startDate and endDate
  const allDates = eachDayOfInterval({ start: startDate, end: endDate })

  return allDates.map(d => format(d, 'yyyy-MM-dd'))
})

const itemsByDate = computed((): {
  [index: string]: Array<LessonListResource>
} => {
  return groupBy(x => x.date, [
    ...lessons.value,
  ])
})

async function loadLessons() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<LessonListResource>>(`lessons`, {
    params: {
      year: filters.value.year,
    },
  })
  if (data.value) {
    lessons.value = data.value.data
  }
  loading.value = false
}

async function loadVacations() {
  vacations.value = {}
  const { data } = await useHttp<ApiResponse<VacationResource>>(`common/vacations`, {
    params: { year: filters.value.year },
  })
  if (data.value) {
    for (const { date } of data.value.data) {
      vacations.value[date] = true
    }
  }
}

async function loadData() {
  await loadLessons()
  await loadVacations()
}

watch(filters, (newVal) => {
  loadData()
  saveFilters(newVal)
}, { deep: true })

nextTick(loadData)
</script>

<template>
  <UiFilters>
    <v-select
      v-model="filters.year"
      label="Учебный год"
      :items="selectItems(YearLabel)"
      density="comfortable"
    />
  </UiFilters>
  <UiLoader v-if="loading" />
  <div v-else class="all-lesson-list">
    <div
      v-for="d in dates"
      :key="d"
      :class="{
        'week-separator': getDay(d) === 0,
        'all-lesson-list--vacation': vacations[d] === true,
      }"
    >
      <div>
        {{ formatTextDate(d) }}
        <span class="text-gray ml-1">
          {{ dayLabels[getDay(d)] }}
        </span>
      </div>
      <LessonItem
        v-for="item in itemsByDate[d]"
        :key="`l-${item.id}`"
        :item="item"
        @edit="lessonDialog?.edit"
      />
    </div>
  </div>
</template>

<style lang="scss">
.all-lesson-list {
  & > div {
    --height: 57px;
    position: relative;
    min-height: var(--height);
    display: flex;
    flex-direction: column;
    padding: 16px 20px;
    border-bottom: thin solid
      rgba(var(--v-border-color), var(--v-border-opacity));
    gap: 20px;
    &.week-separator {
      border-bottom: 2px solid rgb(var(--v-theme-gray));
      // margin-bottom: var(--height);
      // &:after {
      //   content: '';
      //   background: #fafafa;
      //   position: absolute;
      //   bottom: -58px;
      //   left: 0;
      //   width: 100%;
      //   height: var(--height);
      //   border-bottom: thin solid
      //     rgba(var(--v-border-color), var(--v-border-opacity));
      // }
    }
    & > div {
      &:not(:first-child) {
        display: flex;
        align-items: center;
        column-gap: 20px;
        row-gap: 10px;
        flex-wrap: wrap;
      }
      & > div:last-child {
        flex: 1;
        position: relative;
        .v-chip {
          top: -16px;
          position: absolute;
        }
      }
      &:first-child {
        position: absolute;
        top: 16px;
        left: 20px;
      }
    }
  }
  &--vacation {
    background: rgba(var(--v-theme-red), 0.1);
  }
}
</style>

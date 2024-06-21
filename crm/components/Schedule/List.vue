<script setup lang="ts">
import { eachDayOfInterval, endOfMonth, format, getDay, getMonth, startOfMonth } from 'date-fns'
// import ScheduleClientItem from '~/components/Schedule/ClientItem.vue'
import type { LessonDialog } from '#build/components'

const { id, entity } = defineProps<{
  entity: Extract<EntityString, 'client' | 'teacher' | 'group'>
  id: number
}>()
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()
const dayLabels = ['пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс']
const year = ref<Year>(2023)
const schedule = ref<Schedule>({})
const loading = ref(true)

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
  const start = day - 1
  const remainder = (dates.value.length + start) % 7
  const end = remainder === 0 ? 0 : 7 - remainder
  return { start, end }
})

const editable = computed(() => entity === 'group')

// const currentComponent = computed(() => {
//   switch (entity) {
//     case 'client':
//       return ScheduleClientItem
//     default:
//       return ScheduleClientItem
//   }
// })

watch(year, loadData)

async function loadData() {
  loading.value = true
  const { data } = await useHttp<Schedule>(`schedule/${entity}/${id}`, {
    params: {
      year: year.value,
    },
  })
  if (data.value) {
    schedule.value = data.value
  }
  loading.value = false
}

function formatCalendarDate(d: string) {
  const month = getMonth(d)
  return format(d, `d ${MonthLabel[month]}`)
}

nextTick(loadData)
</script>

<template>
  <div class="schedule">
    <div class="filters">
      <div class="filters-inputs" style="justify-content: space-between; align-items: center; width: 100%">
        <div>
          <v-select
            v-model="year"
            :disabled="editable"
            label="Учебный год"
            :items="selectItems(YearLabel)"
            density="comfortable"
          />
        </div>
        <v-btn
          v-if="editable"
          color="primary"
          @click="lessonDialog?.create(id)"
        >
          добавить занятие
        </v-btn>
      </div>
    </div>
    <div class="schedule-calendar__wrapper">
      <!-- <UiWhiteLoader :loading="loading" /> -->
      <div class="schedule-calendar">
        <div
          v-for="dayLabel in dayLabels" :key="dayLabel"
          class="schedule-calendar__header"
        >
          {{ dayLabel }}
        </div>
        <div v-for="i in offset.start" :key="i" />
        <div v-for="d in dates" :key="d">
          <div class="schedule-calendar__date">
            {{ formatCalendarDate(d) }}
          </div>
          <div class="schedule-calendar__lessons">
            <ScheduleItem
              v-for="l in schedule[d]"
              :key="l.id"
              :item="l"
            />
          </div>
        </div>
        <div v-for="i in offset.end" :key="i" />
      </div>
    </div>
  </div>
  <LessonDialog
    v-if="editable"
    ref="lessonDialog"
    @updated="loadData"
    @destroyed="loadData"
  />
</template>

<style lang="scss">
.schedule {
  &-calendar {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    & > div {
      min-height: 120px;
      padding: 10px 10px 20px;
      border-bottom: 1px solid #e0e0e0;
      border-right: 1px solid #e0e0e0;
      position: relative;
    }
    &__date {
      color: rgb(var(--v-theme-gray));
      font-size: 12px;
      position: absolute;
      right: 6px;
      bottom: 2px;
    }
    &__header {
      color: rgb(var(--v-theme-gray));
      min-height: initial !important;
    }
    &__lessons {
      display: flex;
      flex-direction: column;
      gap: 20px;
      & > div {
        font-size: 14px;
        line-height: 20px;
        position: relative;
        & > div {
          display: flex;
          align-items: center;
          gap: 4px;
          overflow: hidden; /* Hide overflow content */
          text-overflow: ellipsis; /* Add ellipsis for overflowing text */
          white-space: nowrap; /* Prevent text from wrapping */
          .v-icon {
            font-size: 18px;
          }
        }
      }
      .table-actionss {
        width: 50px;
        right: -10px !important;
        top: -6px !important;
        padding-top: 0 !important;
      }
    }
    &__wrapper {
      position: relative;
    }
  }
}
</style>

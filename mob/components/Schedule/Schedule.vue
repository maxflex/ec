<script setup lang="ts">
import { eachDayOfInterval, endOfMonth, format, getDay, startOfMonth } from 'date-fns'
import { groupBy } from 'rambda'
import { Vue3SlideUpDown } from 'vue3-slide-up-down'
import { formatDateMonth } from '~/utils'

const { groupId, teacherId, clientId, program, year, programFilter } = defineProps<{
  groupId?: number
  teacherId?: number
  clientId?: number
  year?: Year
  program?: Program
  programFilter?: boolean
  headTeacher?: boolean
}>()

const expanded = ref<Record<string, boolean>>({})

// const tabName = teacherId ? 'TeacherSchedule' : groupId ? 'GroupSchedule' : 'ClientSchedule'
const selectedProgram = ref<Program>()
const selectedYear = ref<Year>()
const dayLabels = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']
const params = {
  // только один из них НЕ undefined
  teacher_id: teacherId,
  client_id: clientId,
  group_id: groupId,
}
const loading = ref(true)
const lessons = ref<LessonListResource[]>([])
const events = ref<EventListResource[]>([])
const vacations = ref<Record<string, boolean>>({})
const examDates = ref<ExamDateResource[]>([])

if (year) {
  selectedYear.value = year
  loadData()
}

interface AvailableExamDates {
  [key: string]: Array<{
    id: number
    exam: Exam
    is_reserve: number
  }>
}

const availableExamDates = computed<AvailableExamDates>(() => {
  const result = {} as AvailableExamDates
  for (const examDate of examDates.value) {
    if (selectedProgram.value && !examDate.programs.includes(selectedProgram.value)) {
      continue
    }
    for (const d of examDate.dates) {
      if (!(d.date in result)) {
        result[d.date] = []
      }
      result[d.date].push({
        id: examDate.id,
        exam: examDate.exam,
        is_reserve: d.is_reserve,
      })
    }
  }
  return result
})

const filteredLessons = computed(() =>
  selectedProgram.value
    ? lessons.value.filter(l => l.group.program === selectedProgram.value)
    : lessons.value,
)

const dates = computed(() => {
  if (!selectedYear.value) {
    return []
  }

  // Define the start and end months for the academic year
  const startMonth = 8 // September (0-indexed)
  const endMonth = 5 // June (0-indexed)

  // Define start and end dates for the academic year
  const startDate = startOfMonth(new Date(selectedYear.value, startMonth, 1)) // September 1st
  const endDate = endOfMonth(new Date(selectedYear.value + 1, endMonth, 31)) // May 31st

  // Generate array of all dates between startDate and endDate
  const allDates = eachDayOfInterval({ start: startDate, end: endDate })

  const result = []
  for (const d of allDates) {
    const dateString = format(d, 'yyyy-MM-dd')
    if (
      filteredLessons.value.some(e => e.date === dateString)
      || events.value.some(e => e.date === dateString)
      || (dateString in availableExamDates.value)
    ) {
      result.push(dateString)
    }
  }
  return result
})

const itemsByDate = computed(
  (): {
    [index: string]: Array<LessonListResource | EventListResource>
  } => {
    return groupBy(x => x.date, [...filteredLessons.value, ...events.value])
  },
)

const availableYears = ref<Year[]>()
const availablePrograms = computed(() => [...new Set(lessons.value.map(l => l.group.program))])

async function loadAvailableYears() {
  const { data } = await useHttp<Year[]>(
    `groups`,
    {
      params: {
        ...params,
        available_years: 1,
      },
    },
  )
  if (data.value) {
    availableYears.value = data.value
    if (data.value.length > 0) {
      selectedYear.value = data.value[0]
    }
  }
}

async function loadLessons() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<LessonListResource>>(
    `lessons`,
    {
      params: {
        ...params,
        year: groupId ? undefined : selectedYear.value,
      },
    },
  )
  if (data.value) {
    lessons.value = data.value.data
  }
  loading.value = false
}

async function loadEvents() {
  // в группе не может быть событий
  if (groupId) {
    return
  }
  const { data } = await useHttp<ApiResponse<EventListResource>>(
    `events`,
    {
      params: {
        ...params,
        year: selectedYear.value,
      },
    },
  )
  if (data.value) {
    events.value = data.value.data
  }
}

async function loadVacations() {
  vacations.value = {}
  const { data } = await useHttp<ApiResponse<VacationResource>>(
    `vacations`,
    {
      params: { year: selectedYear.value },
    },
  )
  if (data.value) {
    for (const { date } of data.value.data) {
      vacations.value[date] = true
    }
  }
}

async function loadExamDates() {
  const programs = program ? [program] : availablePrograms.value
  const { data } = await useHttp<ApiResponse<ExamDateResource>>(
    `exam-dates`,
    {
      params: {
        'programs[]': programs,
      },
    },
  )
  if (data.value) {
    examDates.value = data.value.data
  }
}

function isEvent(item: LessonListResource | EventListResource): item is EventListResource {
  return 'participants_count' in item
}

async function loadData() {
  selectedProgram.value = undefined
  if (!selectedYear.value) {
    return
  }
  await loadLessons()
  await loadExamDates()
  await loadEvents()
  await loadVacations()
}

function expand(d: string) {
  if (expanded.value[d]) {
    expanded.value[d] = false
  }
  else {
    expanded.value[d] = true
  }
}

watch(selectedYear, loadData)

nextTick(loadAvailableYears)
</script>

<template>
  <UiFilters>
    <!-- на странице группы год передаётся явно, там селектор не нужен (v-if="!year") -->
    <AvailableYearsSelector v-if="!year" v-model="selectedYear" :items="availableYears" />
    <UiClearableSelect
      v-if="programFilter"
      v-model="selectedProgram"
      label="Программа"
      :items="
        availablePrograms.map((value) => ({
          value,
          title: ProgramLabel[value],
        }))
      "
      density="comfortable"
    />
  </UiFilters>
  <UiNoData v-if="availableYears !== undefined && !selectedYear" />
  <UiLoader v-else-if="loading" />
  <div v-else class="schedule">
    <div
      v-for="d in dates"
      :key="d"
      class="schedule__row"
      :class="{
        'schedule__row--expanded': expanded[d],
      }"
    >
      <div class="schedule__row-data" @click="expand(d)">
        <div>
          {{ formatDateMonth(d) }}
          <span class="text-gray ml-1">
            {{ dayLabels[getDay(d)] }}
          </span>
        </div>
        <div>
          <div v-if="itemsByDate[d] && itemsByDate[d].filter(e => !isEvent(e)).length">
            {{ plural(itemsByDate[d].filter(e => !isEvent(e)).length, ['занятие', 'занятия', 'занятий']) }}
          </div>
          <div v-if="itemsByDate[d] && itemsByDate[d].filter(e => isEvent(e)).length">
            {{ plural(itemsByDate[d].filter(e => isEvent(e)).length, ['событие', 'события', 'событий']) }}
          </div>
          <div v-if="availableExamDates[d]">
            {{ plural(availableExamDates[d].length, ['экзамен', 'экзамена', 'экзаменов']) }}
          </div>
        </div>
        <div>
          <v-icon
            icon="$expand"
            color="gray"
          />
        </div>
      </div>
      <Vue3SlideUpDown
        :model-value="!!expanded[d]"
        :duration="200"
        class="schedule__row-expansion"
      >
        <div v-if="vacations[d]" class="lesson-item lesson-item__extra lesson-item__extra--vacation">
          Государственный праздник
        </div>
        <template v-if="d in availableExamDates">
          <div
            v-for="ed in availableExamDates[d]"
            :key="ed.id"
            class="lesson-item lesson-item__extra lesson-item__extra--exam-date"
            :class="{
              'lesson-item__extra--exam-date-reserved': ed.is_reserve,
            }"
          >
            {{ ExamLabel[ed.exam] }}
            <template v-if="ed.is_reserve">
              – резервная дата
            </template>
            <template v-else>
              – основная дата
            </template>
          </div>
        </template>
        <template v-for="item in itemsByDate[d]">
          <ScheduleRowEvent
            v-if="isEvent(item)"
            :key="`e-${item.id}`"
            :item="item"
          />
          <ScheduleRowLesson
            v-else
            :id="`lesson-${item.id}`"
            :key="`l-${item.id}`"
            :item="item"
          />
        </template>
      </Vue3SlideUpDown>
    </div>
  </div>
</template>

<style lang="scss">
.schedule {
  &__row {
    &-data {
      display: flex;
      align-items: center;
      border-bottom: 1px solid rgb(var(--v-theme-bg));
      transition: background-color ease-in-out 0.2s;
      & > div {
        font-size: 14px;
        &:first-child {
          width: 120px;
          padding-left: var(--offset);
        }
        &:nth-child(2) {
          display: inline-flex;
          flex: 1;
          & > div:not(:last-child) {
            padding-right: 3px;
            &:after {
              content: ',';
            }
          }
        }
        &:last-child {
          width: fit-content;
          padding-right: 10px;
          .v-icon {
            opacity: 0.5;
            transition: transform ease-in-out 0.2s;
          }
        }
        padding-top: 10px;
        padding-bottom: 10px;
      }
    }
    &-expansion {
      border-bottom: 1px solid rgb(var(--v-theme-bg));
      & > div {
        padding: 10px var(--offset);
      }
    }
    &--expanded {
      .schedule__row-data {
        background-color: rgba(var(--v-theme-secondary), 0.05);
        border-color: transparent;
        .v-icon {
          transform: rotate(-180deg);
        }
      }
    }
  }
}
</style>

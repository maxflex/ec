<script setup lang="ts">
import { differenceInDays, eachDayOfInterval, format, getDay } from 'date-fns'
import { groupBy } from 'lodash-es'
import { Vue3SlideUpDown } from 'vue3-slide-up-down'
import { formatDateMonth } from '~/utils'

const { groupId, teacherId, clientId, year, programFilter } = defineProps<{
  groupId?: number
  teacherId?: number
  clientId?: number
  year?: Year
  program?: Program
  programFilter?: boolean
  headTeacher?: boolean
}>()

const expanded = ref<Record<string, boolean>>({})
const showAllDates = ref(false)
const todayDate = today()

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
const vacations = ref<Record<string, boolean>>({})

if (year) {
  selectedYear.value = year
  loadData()
}

const filteredLessons = computed(() =>
  selectedProgram.value
    ? lessons.value.filter(l => l.group.program === selectedProgram.value)
    : lessons.value,
)

// все даты учебного года
const allDates = computed<string[]>(() => selectedYear.value
  ? eachDayOfInterval({
      start: `${selectedYear.value}-09-01`,
      end: `${selectedYear.value + 1}-06-31`,
    }).map(d => format(d, 'yyyy-MM-dd'))
  : [],
)

// даты с контентом
const dates = computed(() => {
  const result = []
  for (const d of allDates.value) {
    if (hasContentAtDate(d)) {
      // если дата больше чем 3 дня назад или нажата "показать все даты"
      if (showAllDates.value || differenceInDays(todayDate, d) < 3) {
        result.push(d)
      }
    }
  }
  return result
})

function hasContentAtDate(d: string): boolean {
  return filteredLessons.value.some(e => e.date === d)
}

const itemsByDate = computed(
  (): Record<string, Array<LessonListResource>> =>
    groupBy([...filteredLessons.value], 'date'),
)

// если есть скрытый контент, то кнопка "показать более ранние даты"
const hasHiddenContent = computed<boolean>(() => {
  if (showAllDates.value) {
    return false
  }

  // фикс для конца года
  if (dates.value.length === 0 && allDates.value.length !== 0) {
    return true
  }

  const firstShownDate = dates.value[0]

  for (const d of allDates.value) {
    if (d < firstShownDate && hasContentAtDate(d)) {
      return true
    }
  }

  return false
})

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

async function loadData() {
  selectedProgram.value = undefined
  if (!selectedYear.value) {
    return
  }
  await loadLessons()
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
      label="Программа" :items="availablePrograms.map((value) => ({
        value,
        title: ProgramLabel[value],
      }))
      " density="comfortable"
    />
  </UiFilters>
  <UiNoData v-if="availableYears !== undefined && !selectedYear" />
  <UiLoader v-else-if="loading" />
  <div v-else class="schedule">
    <div v-if="hasHiddenContent" class="pb-4 pt-2 pl-4">
      <v-chip label @click="showAllDates = true">
        показать более ранние даты
      </v-chip>
    </div>
    <div
      v-for="d in dates" :key="d" class="schedule__row" :class="{
        'schedule__row--expanded': expanded[d],
        'schedule__row--today': d === todayDate,
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
          {{ plural(itemsByDate[d].length, ['занятие', 'занятия', 'занятий']) }}
        </div>
        <div>
          <v-icon icon="$expand" color="gray" />
        </div>
      </div>
      <Vue3SlideUpDown :model-value="!!expanded[d]" :duration="200" class="schedule__row-expansion">
        <div v-if="vacations[d]" class="lesson-item lesson-item__extra lesson-item__extra--vacation">
          Государственный праздник
        </div>
        <ScheduleRowLesson v-for="item in itemsByDate[d]" :id="`lesson-${item.id}`" :key="`l-${item.id}`" :item="item" />
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

    &--today {
      .schedule__row-data {
        background-color: rgba(var(--v-theme-primary), 0.3);
      }
    }
  }
}
</style>

<script setup lang="ts">
import type {
  EventDialog,
  LessonBulkCreateDialog,
  LessonBulkUpdateDialog,
  LessonConductDialog,
  LessonDialog,
  LessonViewDialog,
} from '#build/components'
import { eachDayOfInterval, endOfMonth, format, getDay, startOfMonth } from 'date-fns'
import { groupBy } from 'rambda'

// потому что props ещё есть ниже
const properties = defineProps<{
  groupId?: number
  teacherId?: number
  clientId?: number
  year?: Year
  program?: Program
  showTeeth?: boolean
}>()

const { groupId, teacherId, clientId, program, showTeeth, year } = properties
const tabName = teacherId ? 'TeacherSchedule' : (groupId ? 'GroupSchedule' : 'ClientSchedule')

const loadedFilters = loadFilters({
  year: currentAcademicYear(),
  hideEmptyDates: 0,
}, tabName)

const filters = ref({
  ...loadedFilters,
  year: year || loadedFilters.year,
})

const { user, isTeacher, isClient } = useAuthStore()
const isMassEditable = user?.entity_type === EntityTypeValue.user && !!groupId
const dayLabels = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']
const params = {
  // только один из них НЕ undefined
  teacher_id: teacherId,
  client_id: clientId,
  group_id: groupId,
}
const loading = ref(true)
const teeth = ref<Teeth>()
const lessons = ref<LessonListResource[]>([])
const events = ref<EventListResource[]>([])
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()
const lessonBulkUpdateDialog = ref<InstanceType<typeof LessonBulkUpdateDialog>>()
const lessonBulkCreateDialog = ref<InstanceType<typeof LessonBulkCreateDialog>>()
const lessonViewDialog = ref<InstanceType<typeof LessonViewDialog>>()
const eventDialog = ref<InstanceType<typeof EventDialog>>()
const conductDialog = ref<InstanceType<typeof LessonConductDialog>>()
const vacations = ref<Record<string, boolean>>({})
const examDates = ref<Record<string, boolean>>({})
const checkboxes = ref<{ [key: number]: boolean }>({})
const lessonIds = computed((): number[] => {
  const result = []
  for (const key in checkboxes.value) {
    if (checkboxes.value[key]) {
      result.push(Number.parseInt(key))
    }
  }
  return result
})
const dates = computed(() => {
  // Define the start and end months for the academic year
  const startMonth = 8 // September (0-indexed)
  const endMonth = 5 // June (0-indexed)

  // Define start and end dates for the academic year
  const startDate = startOfMonth(new Date(filters.value.year, startMonth, 1)) // September 1st
  const endDate = endOfMonth(new Date(filters.value.year + 1, endMonth, 31)) // May 31st

  // Generate array of all dates between startDate and endDate
  const allDates = eachDayOfInterval({ start: startDate, end: endDate })

  const result = []
  for (const d of allDates) {
    const dateString = format(d, 'yyyy-MM-dd')
    if (filters.value.hideEmptyDates) {
      if (
        lessons.value.some(e => e.date === dateString)
        || events.value.some(e => e.date === dateString)
      ) {
        result.push(dateString)
      }
    }
    else {
      result.push(dateString)
    }
  }
  return result
})

const itemsByDate = computed((): {
  [index: string]: Array<LessonListResource | EventListResource>
} => {
  return groupBy(x => x.date, [
    ...lessons.value,
    ...events.value,
  ])
})

async function loadLessons() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<LessonListResource>>(
    `lessons`,
    {
      params: {
        ...params,
        year: groupId ? undefined : filters.value.year,
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
    `common/events`,
    {
      params: {
        ...params,
        year: filters.value.year,
      },
    },
  )
  if (data.value) {
    events.value = data.value.data
  }
}

async function loadTeeth() {
  if (!showTeeth) {
    return
  }
  const { data } = await useHttp<Teeth>(
    `common/teeth`,
    {
      params: {
        ...params,
        year: filters.value.year,
      },
    },
  )
  if (data.value) {
    teeth.value = data.value
  }
}

async function loadVacations() {
  vacations.value = {}
  const { data } = await useHttp<ApiResponse<VacationResource>>(
    `common/vacations`,
    {
      params: { year: filters.value.year },
    },
  )
  if (data.value) {
    for (const { date } of data.value.data) {
      vacations.value[date] = true
    }
  }
}

async function loadExamDates() {
  if (!program) {
    return
  }
  examDates.value = {}
  const { data } = await useHttp<ApiResponse<ExamDateResource>>(
    `common/exam-dates`,
    {
      params: { program },
    },
  )
  if (data.value && data.value.data.length) {
    const { dates } = data.value.data[0]
    for (const date of dates) {
      examDates.value[date] = true
    }
  }
}

function isEvent(item: LessonListResource | EventListResource): item is EventListResource {
  return 'participants_count' in item
}

async function loadData() {
  await loadTeeth()
  await loadLessons()
  await loadEvents()
  await loadExamDates()
  await loadVacations()
}

function onBulkUpdated() {
  loadLessons()
  checkboxes.value = {}
}

function onLessonClick(item: LessonListResource) {
  if (!isMassEditable || item.status === 'cancelled') {
    return
  }
  if (checkboxes.value[item.id]) {
    delete checkboxes.value[item.id]
  }
  else {
    checkboxes.value[item.id] = true
  }
}

watch(() => filters.value.year, loadData)

watch(filters, (newVal) => {
  saveFilters(newVal, tabName)
}, { deep: true })

nextTick(loadData)
</script>

<template>
  <UiFilters>
    <v-select
      v-model="filters.year"
      :disabled="!!groupId"
      label="Учебный год"
      :items="selectItems(YearLabel)"
      density="comfortable"
    />
    <v-select
      v-model="filters.hideEmptyDates"
      label="Даты"
      :items="yesNo('скрыть пустые', 'показывать все', true)"
      density="comfortable"
    />
    <template #buttons>
      <div v-if="Object.keys(checkboxes).length" class="d-flex ga-4">
        <v-btn variant="text" @click="checkboxes = {}">
          отмена
        </v-btn>
        <v-btn
          color="primary" :width="216"
          @click="lessonBulkUpdateDialog?.open(lessonIds)"
        >
          редактировать
          {{ lessonIds.length }}/{{ lessons.length }}
        </v-btn>
      </div>
      <v-menu v-else-if="isMassEditable">
        <template #activator="{ props }">
          <v-btn color="primary" v-bind="props" :width="216">
            добавить занятия
          </v-btn>
        </template>
        <v-list>
          <v-list-item @click="lessonDialog?.create(groupId!, year!)">
            добавить одно занятие
          </v-list-item>
          <v-list-item @click="lessonBulkCreateDialog?.create(groupId!, year!)">
            добавить несколько занятий
          </v-list-item>
        </v-list>
      </v-menu>
      <v-fade-transition v-else>
        <TeethBar v-if="teeth" :items="teeth" />
      </v-fade-transition>
    </template>
  </UiFilters>
  <UiLoader v-if="loading" />
  <div v-else class="schedule">
    <div
      v-for="d in dates"
      :key="d"
      :class="{
        'week-separator': !filters.hideEmptyDates && getDay(d) === 0,
      }"
    >
      <div>
        {{ formatTextDate(d) }}
        <span class="text-gray ml-1">
          {{ dayLabels[getDay(d)] }}
        </span>
      </div>
      <template v-for="item in itemsByDate[d]">
        <EventItem
          v-if="isEvent(item)"
          :key="`e-${item.id}`"
          :item="item"
          @edit="eventDialog?.edit"
        />
        <LessonTeacherItem
          v-else-if="isTeacher"
          :key="`lt-${item.id}`"
          :item="item"
          :checkboxes="checkboxes"
          @click="onLessonClick(item)"
          @edit="lessonDialog?.edit"
          @conduct="conductDialog?.open"
          @view="lessonViewDialog?.open"
        />
        <LessonClientItem
          v-else-if="isClient"
          :key="`lt-${item.id}`"
          :item="item"
          :checkboxes="checkboxes"
          @click="onLessonClick(item)"
          @edit="lessonDialog?.edit"
          @conduct="conductDialog?.open"
          @view="lessonViewDialog?.open"
        />
        <LessonItem
          v-else
          :key="`l-${item.id}`"
          :item="item"
          :checkboxes="checkboxes"
          @click="onLessonClick(item)"
          @edit="lessonDialog?.edit"
          @conduct="conductDialog?.open"
          @view="lessonViewDialog?.open"
        />
      </template>
      <div v-if="vacations[d]" class="schedule-event schedule-event--vacation">
        Государственный праздник
      </div>
      <div v-if="examDates[d]" class="schedule-event schedule-event--exam">
        экзамен
      </div>
    </div>
  </div>
  <LessonViewDialog ref="lessonViewDialog" />
  <LessonDialog ref="lessonDialog" />
  <EventDialog ref="eventDialog" />
  <LessonConductDialog
    ref="conductDialog"
    @updated="loadLessons"
  />
  <template v-if="isMassEditable">
    <LessonBulkUpdateDialog
      ref="lessonBulkUpdateDialog"
      @updated="onBulkUpdated"
    />
    <LessonBulkCreateDialog
      ref="lessonBulkCreateDialog"
      @updated="loadLessons"
    />
  </template>
</template>

<style lang="scss">
.schedule {
  & > div {
    --height: 57px;
    overflow: hidden;
    position: relative;
    min-height: var(--height);
    display: flex;
    flex-direction: column;
    padding: 16px 20px;
    border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
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
  &-event {
    padding: 20px 20px 20px 130px;
    position: relative;
    &:after {
      content: '';
      width: calc(100% - 110px);
      height: 100%;
      position: absolute;
      left: 110px;
      top: 0;
      border-radius: 8px;
      pointer-events: none;
    }
    &--vacation {
      &:after {
        background: rgba(var(--v-theme-red), 0.08);
      }
    }
    &--exam {
      &:after {
        background: rgba(var(--v-theme-success), 0.08);
      }
    }
  }
}
</style>

<script setup lang="ts">
import { eachDayOfInterval, endOfMonth, format, getDay, startOfMonth } from 'date-fns'
import { groupBy } from 'rambda'
import type {
  EventDialog,
  LessonBatchCreateDialog,
  LessonBatchUpdateDialog,
  LessonConductDialog,
  LessonDialog,
} from '#build/components'

const { entity, id, group, showTeeth } = defineProps<{
  entity: Extract<EntityString, 'client' | 'teacher' | 'group'>
  id: number
  group?: GroupResource
  showTeeth?: boolean
}>()

const { user } = useAuthStore()
const editable = user?.entity_type === EntityType.user
const dayLabels = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']
const year = ref<Year>(group === undefined ? currentAcademicYear() : group.year)
const loading = ref(false)
const hideEmptyDates = ref(0)
const teeth = ref<Teeth>()
const lessons = ref<LessonListResource[]>([])
const events = ref<EventListResource[]>([])
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()
const lessonBatchUpdateDialog = ref<InstanceType<typeof LessonBatchUpdateDialog>>()
const lessonBatchCreateDialog = ref<InstanceType<typeof LessonBatchCreateDialog>>()
const eventDialog = ref<InstanceType<typeof EventDialog>>()
const conductDialog = ref<InstanceType<typeof LessonConductDialog>>()
const vacations = ref<Record<string, boolean>>({})
const examDates = ref<Record<string, boolean>>({})
const checkboxes = ref<{ [key: number]: boolean }>({})
const lessonIds = computed((): number[] => {
  const result = []
  for (const key in checkboxes.value) {
    if (checkboxes.value[key] === true) {
      result.push(Number.parseInt(key))
    }
  }
  return result
})
const dates = computed(() => {
  // Define the start and end months for the academic year
  const startMonth = 8 // September (0-indexed)
  const endMonth = 4 // May (0-indexed)

  // Define start and end dates for the academic year
  const startDate = startOfMonth(new Date(year.value, startMonth, 1)) // September 1st
  const endDate = endOfMonth(new Date(year.value + 1, endMonth, 31)) // May 31st

  // Generate array of all dates between startDate and endDate
  const allDates = eachDayOfInterval({ start: startDate, end: endDate })

  const result = []
  for (const d of allDates) {
    const dateString = format(d, 'yyyy-MM-dd')
    if (hideEmptyDates.value) {
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
  const { data } = await useHttp<LessonListResource[]>(`schedule/${entity}/${id}`, {
    params: {
      year: year.value,
    },
  })
  if (data.value) {
    lessons.value = data.value
  }
  loading.value = false
}

async function loadEvents() {
  if (entity === 'group') {
    return
  }
  const { data } = await useHttp<ApiResponse<EventListResource[]>>(`common/events`, {
    params: {
      year: year.value,
      [`${entity}_id`]: id,
    },
  })
  if (data.value) {
    events.value = data.value.data
  }
}

async function loadTeeth() {
  if (!showTeeth) {
    return
  }
  const { data } = await useHttp<Teeth>(`teeth`, {
    params: {
      year: year.value,
      entity_type: EntityType[entity],
      entity_id: id,
    },
  })
  if (data.value) {
    teeth.value = data.value
  }
}

async function loadVacations() {
  vacations.value = {}
  const { data } = await useHttp<ApiResponse<VacationResource[]>>(
      `common/vacations`,
      {
        params: { year: year.value },
      },
  )
  if (data.value) {
    for (const { date } of data.value.data) {
      vacations.value[date] = true
    }
  }
}

async function loadExamDates() {
  if (!group || !group.program) {
    return
  }
  examDates.value = {}
  const { data } = await useHttp<ApiResponse<ExamDateResource[]>>(
      `common/exam-dates`,
      {
        params: {
          program: group.program,
        },
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

function isConductable(item: LessonListResource) {
  if (user?.entity_type === EntityType.teacher) {
    return true
  }
  if (user?.entity_type === EntityType.client) {
    return false
  }
  return item.status === 'conducted'
}

async function loadData() {
  await loadTeeth()
  await loadLessons()
  await loadEvents()
  await loadExamDates()
  await loadVacations()
}

function onBatchUpdated() {
  loadLessons()
  checkboxes.value = {}
}

// function onLessonUpdated(l: LessonListResource) {
//   for (const d in schedule.value) {
//     const index = schedule.value[d].findIndex(e => e.id === l.id)
//     if (index !== -1) {
//       schedule.value[d][index] = l as ScheduleItem
//     }
//     else {
//       schedule.value[d].push(l)
//     }
//   }
// }

// function onLessonDestroyed(l: LessonListResource) {
//   const index = lessons.value.findIndex(e => e.id === l.id)
//   if (index !== -1) {
//     lessons.value.splice(index, 1)
//   }
// }

watch(year, loadData)

nextTick(loadData)
</script>

<template>
  <div class="filters">
    <div class="filters-inputs">
      <v-select
        v-model="year"
        :disabled="group !== undefined"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
      <v-select
        v-model="hideEmptyDates"
        label="Даты"
        :items="yesNo('скрыть пустые', 'показывать все', true)"
        density="comfortable"
      />
    </div>
    <v-menu v-if="editable && group">
      <template #activator="{ props }">
        <v-btn color="primary" v-bind="props">
          добавить занятия
        </v-btn>
      </template>
      <v-list>
        <v-list-item @click="lessonDialog?.create(id, group?.year!)">
          добавить одно занятие
        </v-list-item>
        <v-list-item @click="lessonBatchCreateDialog?.create(id, group?.year!)">
          добавить несколько занятий
        </v-list-item>
      </v-list>
    </v-menu>
    <v-fade-transition v-else>
      <TeethBar v-if="teeth" :items="teeth" />
    </v-fade-transition>
  </div>
  <UiLoaderr v-if="loading" />
  <div v-else class="lesson-list">
    <div
      v-for="d in dates"
      :key="d"
      :class="{
        'week-separator': !hideEmptyDates && getDay(d) === 0,
        'lesson-list--vacation': vacations[d] === true,
        'lesson-list--exam': examDates[d] === true,
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
        <LessonItem
          v-else
          :key="`l-${item.id}`"
          :item="item"
          :conductable="isConductable(item)"
          :editable="editable"
          @edit="lessonDialog?.edit"
          @conduct="conductDialog?.open"
        >
          <template #checkbox>
            <v-checkbox v-model="checkboxes[item.id]" />
          </template>
        </LessonItem>
      </template>
    </div>
  </div>
  <v-slide-y-reverse-transition>
    <div v-if="lessonIds.length" class="bottom-bar">
      <div>
        выбрано:
        <span class="text-gray">
          {{ lessons.length }} /
        </span>
        {{ lessonIds.length }}
      </div>
      <div class="d-flex ga-4">
        <v-btn variant="text" @click="checkboxes = {}">
          отмена
        </v-btn>
        <v-btn
          v-if="editable"
          color="primary"
          @click="lessonBatchUpdateDialog?.open(lessonIds)"
        >
          редактировать
        </v-btn>
      </div>
    </div>
  </v-slide-y-reverse-transition>
  <template v-if="editable">
    <LessonDialog
      ref="lessonDialog"
    />
    <LessonBatchUpdateDialog
      ref="lessonBatchUpdateDialog"
      @updated="onBatchUpdated"
    />
    <LessonBatchCreateDialog
      ref="lessonBatchCreateDialog"
      @updated="loadLessons"
    />
  </template>
  <LessonConductDialog
    ref="conductDialog"
    @updated="loadLessons"
  />
  <EventDialog ref="eventDialog" />
</template>

<style lang="scss">
.lesson-list {
  & > div {
    --height: 57px;
    overflow: hidden;
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
  &__status {
    &--cancelled {
      opacity: 0.4;
    }
  }
  &--vacation {
    background: rgba(var(--v-theme-red), 0.1);
  }
  &--exam {
    background: rgba(var(--v-theme-orange), 0.1) !important;
  }
}
.bottom-bar {
  position: absolute;
  bottom: 0;
  left: 255px;
  padding: 0 20px;
  height: 57px;
  z-index: 3;
  background: #fafafa;
  width: calc(100vw - 255px);
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  // border-top: 2px solid rgb(var(--v-theme-gray));
}
</style>

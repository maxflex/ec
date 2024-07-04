<script setup lang="ts">
import { eachDayOfInterval, endOfMonth, format, getDay, startOfMonth } from 'date-fns'
import { groupBy } from 'rambda'
import type { EventDialog, LessonConductDialog, LessonDialog } from '#build/components'

const { entity, id, editable, conductable, group } = defineProps<{
  entity: Extract<EntityString, 'client' | 'teacher' | 'group'>
  id: number
  editable?: boolean
  conductable?: boolean
  group?: GroupResource
}>()

const dayLabels = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']
const year = ref<Year>(group === undefined ? currentAcademicYear() : group.year)
const loading = ref(false)
const lessons = ref<LessonListResource[]>([])
const events = ref<EventListResource[]>([])
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()
const eventDialog = ref<InstanceType<typeof EventDialog>>()
const conductDialog = ref<InstanceType<typeof LessonConductDialog>>()
const vacations = ref<Record<string, boolean>>({})
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
  const { data } = await useHttp<ApiResponse<EventListResource[]>>(`events`, {
    params: {
      year: year.value,
      [`${entity}_id`]: id,
    },
  })
  if (data.value) {
    events.value = data.value.data
  }
}

async function loadVacations() {
  vacations.value = {}
  const { data } = await useHttp<ApiResponse<VacationResource[]>>(`vacations`, {
    params: { year: year.value },
  })
  if (data.value) {
    for (const { date } of data.value.data) {
      vacations.value[date] = true
    }
  }
}

function isEvent(item: LessonListResource | EventListResource): item is EventListResource {
  return 'participants_count' in item
}

async function loadData() {
  await loadLessons()
  await loadEvents()
  await loadVacations()
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
    <div class="filters-inputs" style="justify-content: space-between; align-items: center; width: 100%">
      <div>
        <v-select
          v-model="year"
          :disabled="group !== undefined"
          label="Учебный год"
          :items="selectItems(YearLabel)"
          density="comfortable"
        />
      </div>
      <v-btn
        v-if="editable && group"
        color="primary"
        @click="lessonDialog?.create(id, group?.year!)"
      >
        добавить занятие
      </v-btn>
    </div>
  </div>
  <UiLoaderr v-if="loading" />
  <div v-else class="lesson-list">
    <div
      v-for="d in dates"
      :key="d"
      :class="{
        'week-separator': getDay(d) === 0,
        'lesson-list--vacation': vacations[d] === true,
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
          v-else :key="`l-${item.id}`"
          :item="item"
          :conductable="conductable"
          :editable="editable"
          @edit="lessonDialog?.edit"
          @conduct="conductDialog?.open"
        />
      </template>
    </div>
  </div>
  <LessonDialog
    v-if="editable"
    ref="lessonDialog"
  />
  <LessonConductDialog
    v-else-if="conductable"
    ref="conductDialog"
    @updated="loadLessons()"
  />
  <EventDialog ref="eventDialog" />
</template>

<style lang="scss">
.lesson-list {
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
        gap: 20px;
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
}
</style>

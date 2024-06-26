<script setup lang="ts">
import { mdiAccountGroup } from '@mdi/js'
import { eachDayOfInterval, endOfMonth, format, getDay, getMonth, getWeek, startOfMonth } from 'date-fns'
import type { LessonConductDialog, LessonDialog } from '#build/components'

const { entity, id, editable, conductable, group } = defineProps<{
  entity: Extract<EntityString, 'client' | 'teacher' | 'group'>
  id: number
  editable?: boolean
  conductable?: boolean
  group?: GroupResource
}>()

const dayLabels = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']
const year = ref<Year>(group === undefined ? 2023 : group.year)
const loading = ref(false)
const schedule = ref<Schedule>({})
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()
const conductDialog = ref<InstanceType<typeof LessonConductDialog>>()
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
        'lesson-list--exam-date': group && group.exam_date === d,
      }"
    >
      <div>
        {{ formatCalendarDate(d) }}
        <span class="text-gray ml-1">
          {{ dayLabels[getDay(d)] }}
        </span>
      </div>
      <div v-for="l in schedule[d]" :id="`lesson-${l.id}`" :key="l.id">
        <div v-if="editable || conductable" class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            color="gray"
            @click="editable ? lessonDialog?.edit(l.id) : conductDialog?.open(l.id, l.status)"
          />
        </div>
        <div style="width: 110px" />
        <div style="width: 120px">
          {{ l.time }} – {{ l.time_end }}
        </div>
        <div style="width: 80px">
          К–{{ l.cabinet }}
        </div>
        <div v-if="l.teacher" style="width: 150px">
          <NuxtLink
            :to="{ name: 'teachers-id', params: { id: l.teacher.id } }"
          >
            {{ formatNameShort(l.teacher) }}
          </NuxtLink>
        </div>
        <div style="width: 90px">
          <NuxtLink :to="{ name: 'groups-id', params: { id: l.group.id } }">
            ГР-{{ l.group.id }}
          </NuxtLink>
        </div>
        <div style="width: 120px">
          {{ ProgramShortLabel[l.group.program] }}
        </div>
        <div style="width: 80px; display: flex; align-items: center">
          <v-icon :icon="mdiAccountGroup" class="mr-2 vfn-1" />
          {{ l.group.contracts_count }}
        </div>
        <div style="width: 140px">
          <LessonStatus2 :status="l.status" />
        </div>
        <div>
          <v-chip v-if="l.is_first" class="text-deepOrange">
            первое
          </v-chip>
          <v-chip v-else-if="l.is_unplanned" class="text-purple">
            внеплановое
          </v-chip>
        </div>
      </div>
    </div>
  </div>
  <LessonDialog
    v-if="editable"
    ref="lessonDialog"
  />
  <LessonConductDialog
    v-else-if="conductable"
    ref="conductDialog"
    @updated="loadData()"
  />
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
  &--exam-date {
    background: rgba(var(--v-theme-orange), 0.2);
  }
}
</style>

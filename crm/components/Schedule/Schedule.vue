<script setup lang="ts">
import type {
  ClientLessonEditPriceDialog,
  LessonBulkCreateDialog,
  LessonBulkUpdateDialog,
  LessonConductDialog,
  LessonDialog,
} from '#build/components'
import {
  LessonItemAdminClient,
  LessonItemAdminGroup,
  LessonItemAdminTeacher,
  LessonItemClientLK,
  LessonItemHeadTeacherLK,
  LessonItemTeacherLK,
} from '#components'
import { mdiCalendarClock } from '@mdi/js'
import { eachDayOfInterval, endOfMonth, format, getDay, startOfMonth } from 'date-fns'
import { groupBy } from 'lodash-es'
import { formatDateMonth } from '~/utils'

const { group, teacherId, clientId, programFilter, headTeacher } = defineProps<{
  group?: GroupResource
  teacherId?: number
  clientId?: number
  programFilter?: boolean
  headTeacher?: boolean
}>()

const selectedProgram = ref<Program>()
const selectedYear = ref<Year>()
const hideEmptyDates = ref<number>(1)
const { user, isTeacher, isClient } = useAuthStore()
const isMassEditable = user?.entity_type === EntityTypeValue.user && group
const dayLabels = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']
const params = {
  // только один из них НЕ undefined
  teacher_id: teacherId,
  client_id: clientId,
  group_id: group?.id,
}
const loading = ref(true)
const teeth = ref<Teeth>()
const lessons = ref<LessonListResource[]>([])
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()
const clientLessonEditPriceDialog = ref<InstanceType<typeof ClientLessonEditPriceDialog>>()
const lessonBulkUpdateDialog = ref<InstanceType<typeof LessonBulkUpdateDialog>>()
const lessonBulkCreateDialog = ref<InstanceType<typeof LessonBulkCreateDialog>>()
const conductDialog = ref<InstanceType<typeof LessonConductDialog>>()
const vacations = ref<Record<string, boolean>>({})
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

if (group) {
  selectedYear.value = group.year
  loadData()
}

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
    if (hideEmptyDates.value) {
      if (
        filteredLessons.value.some(e => e.date === dateString)
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

const itemsByDate = computed(
  (): Record<string, Array<LessonListResource>> =>
    groupBy([...filteredLessons.value], 'date'),
)

const availableYears = ref<Year[]>()
const availablePrograms = computed(() => [...new Set(lessons.value.map(l => l.group.program))])

async function loadAvailableYears() {
  // в расписании группы не подгружаем
  if (group) {
    return
  }

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
        year: group ? undefined : selectedYear.value,
      },
    },
  )
  if (data.value) {
    lessons.value = data.value.data
  }
  loading.value = false
}

async function loadTeeth() {
  if (group) {
    return
  }
  const { data } = await useHttp<Teeth>(
    `teeth`,
    {
      params: {
        ...params,
        year: selectedYear.value,
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
  await loadTeeth()
  await loadLessons()
  await loadVacations()
}

function onBulkUpdated() {
  loadLessons()
  checkboxes.value = {}
}

function onMassEditClick(item: LessonListResource) {
  if (!isMassEditable || item.status === 'cancelled' || !lessonIds.value.length) {
    return
  }
  toggleCheckboxes(item.id)
}

function toggleCheckboxes(id: number) {
  if (checkboxes.value) {
    if (checkboxes.value[id]) {
      delete checkboxes.value[id]
    }
    else {
      checkboxes.value[id] = true
    }
  }
}

function selectAll() {
  for (const lesson of lessons.value) {
    checkboxes.value[lesson.id] = true
  }
}

const lessonComponent = (function () {
  if (isClient) {
    console.log('LessonItemHeadTeacher')
    return LessonItemClientLK
  }
  else if (headTeacher) {
    console.log('LessonItemHeadTeacherLK')
    return LessonItemHeadTeacherLK
  }
  else if (clientId) {
    console.log('LessonItemAdminClient')
    return LessonItemAdminClient
  }
  else if (isTeacher) {
    console.log('LessonItemTeacherLK')
    return LessonItemTeacherLK
  }
  else if (teacherId) {
    console.log('LessonItemAdminTeacher')
    return LessonItemAdminTeacher
  }
  console.log('LessonItemAdminGroup')
  return LessonItemAdminGroup
})()

watch(selectedYear, loadData)

nextTick(loadAvailableYears)
</script>

<template>
  <UiFilters>
    <!-- на странице группы год передаётся явно, там селектор не нужен -->
    <AvailableYearsSelector v-if="!group" v-model="selectedYear" :items="availableYears" />
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
    <div v-if="group && group.teeth" style="width: fit-content" class="d-flex ga-2">
      <template v-if="Object.keys(group.teeth).length">
        <TeethBar :items="group.teeth!" />
      </template>
      <span v-else class="text-gray">
        расписание отсутствует
      </span>
    </div>

    <template #buttons>
      <div v-if="lessonIds.length" class="d-flex ga-4">
        <v-btn variant="text" @click="checkboxes = {}">
          отмена
        </v-btn>
        <v-btn variant="text" @click="selectAll()">
          выбрать всё
        </v-btn>
        <v-btn color="primary" :width="216" @click="lessonBulkUpdateDialog?.open(lessonIds, group.program)">
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
          <v-list-item @click="lessonDialog?.create(group.id, group!.year, group.program)">
            добавить одно занятие
          </v-list-item>
          <v-list-item @click="lessonBulkCreateDialog?.create(group.id, group.year, group.program)">
            добавить несколько занятий
          </v-list-item>
        </v-list>
      </v-menu>
      <v-fade-transition v-else>
        <TeethBar v-if="teeth" :items="teeth" />
      </v-fade-transition>
    </template>
  </UiFilters>
  <UiNoData v-if="availableYears !== undefined && !selectedYear" />
  <UiLoader v-else-if="loading" />
  <div v-else class="schedule">
    <div
      v-for="d in dates"
      :key="d"
      :class="{
        'week-separator': !hideEmptyDates && getDay(d) === 0,
      }"
    >
      <div>
        {{ formatDateMonth(d) }}
        <span class="text-gray ml-1">
          {{ dayLabels[getDay(d)] }}
        </span>
      </div>
      <div v-if="vacations[d]" class="lesson-item lesson-item__extra lesson-item__extra--vacation">
        Государственный праздник
      </div>
      <component
        :is="lessonComponent"
        v-for="item in itemsByDate[d]"
        :id="`lesson-${item.id}`"
        :key="`l-${item.id}`"
        :item="item"
        :checkboxes="checkboxes"
        class="lesson-item lesson-item__lesson"
        @edit="lessonDialog?.edit"
        @conduct="conductDialog?.open"
        @edit-price="clientLessonEditPriceDialog?.edit"
        @click="onMassEditClick(item)"
        @select="toggleCheckboxes"
      />
    </div>
  </div>
  <LessonDialog ref="lessonDialog" />
  <ClientLessonEditPriceDialog v-if="clientId" ref="clientLessonEditPriceDialog" />
  <LessonConductDialog ref="conductDialog" @conducted="loadLessons" />
  <template v-if="isMassEditable">
    <LessonBulkUpdateDialog ref="lessonBulkUpdateDialog" @updated="onBulkUpdated" />
    <LessonBulkCreateDialog ref="lessonBulkCreateDialog" @updated="loadLessons" />
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
        align-items: flex-start;
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
}
</style>

<script setup lang="ts">
import type {
  ClientLessonEditPriceDialog,
  LessonBulkCreateDialog,
  LessonBulkUpdateDialog,
  LessonConductDialog,
  LessonDialog,
} from '#build/components'
import type { GroupResource } from '../Group'
import {
  LessonItemAdminClient,
  LessonItemAdminGroup,
  LessonItemAdminTeacher,
  LessonItemClientLK,
  LessonItemHeadTeacherLK,
  LessonItemTeacherLK,
} from '#components'
import { getDay } from 'date-fns'
import { groupBy, uniq } from 'lodash-es'
import { formatDateMonth } from '~/utils'
import { holidays } from '.'
import { school89 } from '../Program'

const { group, teacherId, clientId, programFilter, headTeacher, showHolidays: showHolidaysProp, showCalendar } = defineProps<{
  group?: GroupResource
  teacherId?: number
  clientId?: number
  programFilter?: boolean
  headTeacher?: boolean
  showHolidays?: boolean
  showCalendar?: boolean
}>()

const selectedProgram = ref<Program>()
const selectedYear = ref<Year>()
const { user, isTeacher, isClient } = useAuthStore()
// "зубы" показываются не везде (например, в группе не показываются)
const showTeeth = ref(true)
const isMassEditable = user?.entity_type === EntityTypeValue.user && group
const params = {
  // только один из них НЕ undefined
  teacher_id: teacherId,
  client_id: clientId,
  group_id: group?.id,
}

// режим массового редактирования
const massEditMode = ref(false)
const loading = ref(true)
const teeth = ref<Teeth>()
const lessons = ref<LessonListResource[]>([])
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()
const clientLessonEditPriceDialog = ref<InstanceType<typeof ClientLessonEditPriceDialog>>()
const lessonBulkUpdateDialog = ref<InstanceType<typeof LessonBulkUpdateDialog>>()
const lessonBulkCreateDialog = ref<InstanceType<typeof LessonBulkCreateDialog>>()
const conductDialog = ref<InstanceType<typeof LessonConductDialog>>()
const checkboxes = ref<{ [key: number]: boolean }>({})
const selectedIds = computed((): number[] => {
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

/**
 * Занятия могут быть срезаны по программе на фронте
 */
const filteredLessons = computed<LessonListResource[]>(() =>
  selectedProgram.value
    ? lessons.value.filter(l => l.group.program === selectedProgram.value)
    : lessons.value,
)

/**
 * Показывать каникула когда: установлен параметр + есть занятия 8-9 класса
 */
const showHolidays = computed<boolean>(() => {
  if (!showHolidaysProp) {
    return false
  }

  if (group) {
    return school89.includes(group.program!)
  }

  return lessons.value.some(e => school89.includes(e.group.program))
})

/**
 * Все даты, где есть данные
 * В дате могут быть занятия, могут быть каникулы и другие данные
 */
const allDates = computed<string[]>(() => {
  if (!selectedYear.value) {
    return []
  }

  // все даты занятий
  const dates: string[] = uniq(filteredLessons.value.map(e => e.date)).sort()

  if (showHolidays.value) {
    for (const holidayDate in holidays) {
      // дата уже добавлена
      if (dates.includes(holidayDate)) {
        continue
      }
      // каникулы вставляются только между занятиями
      if (holidayDate > dates[0] && holidayDate < dates[dates.length - 1]) {
        dates.push(holidayDate)
      }
    }
  }

  return dates.sort()
})

/**
 * Занятия, сгруппированные по дате
 * Нужно для O(1) получения всех занятий по дате
 */
const lessonsByDate = computed<Record<string, LessonListResource[]>>(() =>
  groupBy([...filteredLessons.value], 'date'),
)

const availableYears = ref<Year[]>()
const availablePrograms = computed<Program[]>(() => uniq(lessons.value.map(l => l.group.program)))

async function loadAvailableYears() {
  // в группе год всегда один – из параметров группы
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
    else {
      loading.value = false
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
  if (!showTeeth.value) {
    return
  }
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
  teeth.value = data.value!
}

async function loadData() {
  selectedProgram.value = undefined
  if (!selectedYear.value) {
    loading.value = false
    return
  }
  await loadTeeth()
  await loadLessons()
}

function onBulkUpdated() {
  loadLessons()
  checkboxes.value = {}
}

function onMassEditClick(item: LessonListResource, e: MouseEvent) {
  if (!isMassEditable || item.status === 'cancelled' || !massEditMode.value) {
    return
  }
  toggleCheckboxes(item, e.metaKey)
}

/**
 * selectAllTheSame  выбрать все похожие (сгруппированные по времени и дню недели)
 */
function toggleCheckboxes(item: LessonListResource, selectAllTheSame: boolean) {
  const id = item.id
  const isSelected = id in checkboxes.value

  if (selectAllTheSame) {
    const day = getDay(item.date)
    for (const lesson of lessons.value) {
      if (getDay(lesson.date) === day && lesson.time === item.time) {
        if (isSelected) {
          delete checkboxes.value[lesson.id]
        }
        else {
          checkboxes.value[lesson.id] = true
        }
      }
    }
    return
  }

  if (isSelected) {
    delete checkboxes.value[id]
  }
  else {
    checkboxes.value[id] = true
  }
}

function selectAll() {
  for (const lesson of lessons.value) {
    const { id } = lesson
    if (lesson.status === 'cancelled') {
      delete checkboxes.value[id]
      continue
    }
    checkboxes.value[id] = true
  }
}

function cancelMassEdit() {
  massEditMode.value = false
  checkboxes.value = {}
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
    showTeeth.value = false
    return LessonItemAdminClient
  }
  else if (isTeacher) {
    console.log('LessonItemTeacherLK')
    return LessonItemTeacherLK
  }
  else if (teacherId) {
    console.log('LessonItemAdminTeacher')
    showTeeth.value = false
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
      <ScheduleCalendar v-if="showCalendar" :lessons="lessons" class="mr-3" />
      <template v-if="Object.keys(group.teeth).length">
        <TeethBar :items="group.teeth!" />
      </template>
      <span v-else class="text-gray">
        расписания нет
      </span>
    </div>

    <template #buttons>
      <div class="d-flex align-center ga-4">
        <template v-if="massEditMode">
          <v-btn variant="text" @click="cancelMassEdit()">
            отмена
          </v-btn>
          <v-btn variant="text" @click="selectAll()">
            выбрать всё
          </v-btn>
          <v-btn color="primary" :width="220" @click="lessonBulkUpdateDialog?.open(selectedIds, group!.program!)">
            редактировать
            {{ selectedIds.length }}/{{ lessons.length }}
          </v-btn>
        </template>
        <v-menu v-else-if="isMassEditable">
          <template #activator="{ props }">
            <v-btn color="primary" v-bind="props" :width="220">
              добавить занятия
            </v-btn>
          </template>
          <v-list v-if="group">
            <v-list-item @click="lessonDialog?.create(group.id, group.year, group.program!)">
              добавить одно занятие
            </v-list-item>
            <v-list-item @click="lessonBulkCreateDialog?.create(group.id, group.year, group.program!)">
              добавить несколько занятий
            </v-list-item>
            <v-list-item @click="massEditMode = true">
              массовое редактирование
            </v-list-item>
          </v-list>
        </v-menu>

        <v-fade-transition v-else-if="showTeeth">
          <TeethBar v-if="teeth" :items="teeth" />
        </v-fade-transition>
        <ScheduleCalendar v-else-if="showCalendar" :lessons="lessons" />
      </div>
    </template>
  </UiFilters>
  <UiNoData v-if="lessons.length === 0 && !loading" />
  <UiLoader v-else-if="loading" />
  <div v-else class="schedule">
    <template v-for="d in allDates" :key="d">
      <template v-if="showHolidays">
        <div v-if="d in holidays" class="schedule__holidays">
          Каникулы {{ formatDateMonth(d) }} – {{ formatDateMonth(holidays[d]) }}
        </div>
      </template>
      <div v-if="d in lessonsByDate">
        <div>
          {{ formatDateMonth(d) }}
          <span class="text-gray ml-1">
            {{ formatWeekday(d) }}
          </span>
        </div>
        <component
          :is="lessonComponent"
          v-for="item in lessonsByDate[d]"
          :id="`lesson-${item.id}`"
          :key="`l-${item.id}`"
          :item="item"
          :group="group"
          :checkboxes="checkboxes"
          :mass-edit-mode="massEditMode"
          class="lesson-item lesson-item__lesson"
          @edit="lessonDialog?.edit"
          @conduct="conductDialog?.open"
          @edit-price="clientLessonEditPriceDialog?.edit"
          @click="(e: MouseEvent) => onMassEditClick(item, e)"
        />
      </div>
    </template>
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

  &__holidays {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eaf5f1;
    font-size: 24px;
    // font-weight: 500;
  }
}
</style>

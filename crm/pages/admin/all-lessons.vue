<script setup lang="ts">
import type { LessonDialog } from '#build/components'
import { eachDayOfInterval, endOfMonth, format, getDay, startOfMonth } from 'date-fns'
import { Vue3SlideUpDown } from 'vue3-slide-up-down'

interface AllLessons {
  [index: string]: {
    planned_count: number
    conducted_count: number
    cancelled_count: number
    need_conduct_count: number
  }
}

const response = ref<AllLessons>({})
const todayDate = today()

const dayLabels = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']
const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))
const loading = ref(false)
const lessons = ref<LessonListResource[]>([])
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()

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

async function loadData() {
  const { data } = await useHttp<AllLessons>(
    `all-lessons`,
    {
      params: {
        year: filters.value.year,
      },
    },
  )
  response.value = data.value!
}

const expanded = reactive<{
  date: string | null
  loading: boolean
}>({
  date: null,
  loading: false,
})

async function expand(date: string) {
  if (expanded.date === date) {
    expanded.date = null
    return
  }

  expanded.date = date
  expanded.loading = true

  const { data } = await useHttp<ApiResponse<LessonListResource>>(`lessons`, {
    params: {
      date,
    },
  })

  if (data.value) {
    lessons.value = data.value.data.sort((a, b) => a.time.localeCompare(b.time))
  }

  expanded.loading = false
}

watch(filters, async (newVal) => {
  await loadData()
  saveFilters(newVal)
}, { deep: true })

nextTick(() => {
  loadData()
  document
    .querySelector('.all-lessons__today')
    ?.scrollIntoView({
      block: 'center',
    })
})
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
  <div v-else class="all-lessons">
    <div
      v-for="d in dates"
      :key="d"
      :class="{ 'week-separator': getDay(d) === 0 }"
    >
      <div
        :class="{
          'all-lessons__expanded': expanded.date === d,
          'all-lessons__today': todayDate === d,
        }" @click="expand(d)"
      >
        <div>
          {{ formatTextDate(d) }}
          <span class="text-gray ml-1">
            {{ dayLabels[getDay(d)] }}
            <template v-if="todayDate === d">
              (сегодня)
            </template>
          </span>
        </div>
        <template v-if="d in response">
          <div style="width: 200px" class="text-gray">
            {{ formatPrice(response[d].planned_count) }}
            <!--            <span v-if="response[d].need_conduct_count" class="text-error"> -->
            <!--              / {{ response[d].need_conduct_count }} -->
            <!--            </span> -->
          </div>
          <div style="width: 200px" class="text-success">
            {{ formatPrice(response[d].conducted_count) }}
          </div>
          <div style="width: 200px" class="text-error">
            {{ formatPrice(response[d].cancelled_count) }}
          </div>
        </template>
        <div>
          <v-btn
            :loading="expanded.date === d && expanded.loading"
            icon="$expand"
            color="gray"
            :size="48"
            variant="text"
          />
        </div>
      </div>

      <Vue3SlideUpDown :model-value="expanded.date === d && !expanded.loading" :duration="200">
        <LessonItemAdminTeacher
          v-for="lesson in lessons"
          :key="lesson.id"
          class="lesson-item lesson-item__lesson"
          :item="lesson"
          @edit="lessonDialog?.edit"
        />
      </Vue3SlideUpDown>

      <!--      <div class="justify-center"> -->
      <!--        <v-progress-circular indeterminate :size="40" :width="3" /> -->
      <!--      </div> -->
    </div>
  </div>
  <LessonDialog ref="lessonDialog" />
</template>

<style lang="scss">
.all-lessons {
  & > div {
    position: relative;
    display: flex;
    flex-direction: column;
    border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));

    &.week-separator {
      border-bottom: 2px solid rgb(var(--v-theme-gray));
    }
    & > div {
      // date & stats clickable
      &:first-child {
        cursor: pointer;
        display: flex;
        align-items: center;
        padding: 16px 20px;
        button:not(.v-btn--loading) .v-icon {
          transition: transform ease-in-out 0.2s;
        }
        &:hover {
          background: rgb(var(--v-theme-bg));
        }
        & > div {
          &:first-child {
            width: 250px;
          }
          &:last-child {
            flex: 1;
            position: absolute;
            right: 8px;
          }
        }
      }
    }
    .slide-up-down__container {
      display: flex;
      flex-direction: column;
      gap: 20px;

      .lesson-item {
        &:first-child {
          padding-top: 20px;
        }
        &:last-child {
          padding-bottom: 20px;
        }
        display: flex;
        align-items: flex-start;
        column-gap: 20px;
        row-gap: 10px;
        flex-wrap: wrap;

        //--height: 45px;
        //height: var(--height);
        //min-height: var(--height);
        //padding: 16px 20px;
        & > div {
          &:last-child {
            display: none;
          }
        }
      }
    }
  }
  &__expanded {
    button .v-icon {
      transform: rotate(-180deg);
    }
  }
}
</style>

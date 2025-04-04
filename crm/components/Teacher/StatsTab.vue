<script setup lang="ts">
interface TeacherStats {
  lessons: Array<{
    direction: Direction
    conducted: number
    planned: number
  }>
  cancelled_percentage: number
  report_fill_avg: number
  report_similarity: number
  conducted_next_day_count: number
  review_rating_avg: number
  client_lesson_counts: {
    late: number
    online: number
    absent: number
    left: number
    payback: number
  }
}
const { teacherId } = defineProps<{
  teacherId: number
}>()

const availableYears = ref<Year[]>()

const stats = ref<TeacherStats>()

const selectedYear = ref<Year>()

async function loadData() {
  stats.value = undefined
  const { data } = await useHttp<TeacherStats>(
    `teachers/stats/${teacherId}`,
    {
      params: {
        year: selectedYear.value,
      },
    },
  )
  stats.value = data.value!
}

async function loadAvailableYears() {
  const { data } = await useHttp<Year[]>(
    `groups`,
    {
      params: {
        teacher_id: teacherId,
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

watch(selectedYear, loadData)

nextTick(loadAvailableYears)
</script>

<template>
  <UiFilters>
    <AvailableYearsSelector v-model="selectedYear" :items="availableYears" />
  </UiFilters>

  <UiNoData v-if="availableYears && availableYears.length === 0" />
  <UiLoader v-else-if="!stats" />
  <div v-else class="teacher-stats">
    <div class="teacher-stats__row">
      <div>
        Занятия
      </div>
      <div class="teacher-stats__lessons">
        <div v-for="item in stats.lessons" :key="item.direction">
          <div>
            {{ DirectionLabel[item.direction] }}
          </div>
          <div>
            {{ item.conducted || '' }}
            <span v-if="item.planned" class="text-gray">
              <template v-if="item.conducted"> + </template>
              {{ item.planned }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="teacher-stats__blocks mt-6">
      <div>
        <div>
          Средний уровень заполненности отчетов со статусом "опубликовано"
        </div>
        <div style="width: 100px" class="mt-2">
          {{ stats.report_fill_avg }}%
        </div>
      </div>

      <div>
        <div>
          Доля отмен
        </div>
        <div>
          {{ stats.cancelled_percentage }}%
        </div>
      </div>

      <div>
        <div>
          Степень "одинаковости" отчетов
        </div>
        <div>
          {{ stats.report_similarity }}%
        </div>
      </div>
      <div>
        <div>
          Доля занятий, которая была проведена не в день занятия
        </div>
        <div>
          {{ stats.conducted_next_day_count }}%
        </div>
      </div>
      <div>
        <div>
          Доля отсутствующих учеников в проведенных занятиях
        </div>
        <div>
          {{ stats.client_lesson_counts.absent }}%
        </div>
      </div>
      <div>
        <div>
          Доля опаздывающих
        </div>
        <div>
          {{ stats.client_lesson_counts.late }}%
        </div>
      </div>
      <div>
        <div>
          Доля дистанционщиков
        </div>
        <div>
          {{ stats.client_lesson_counts.online }}%
        </div>
      </div>
      <div>
        <div>
          Доля занятий за счет ушедших учеников
        </div>
        <div>
          {{ stats.client_lesson_counts.left }}%
        </div>
      </div>
      <div>
        <div>
          Средняя оценка по отзывам
        </div>
        <div>
          {{ stats.review_rating_avg || 'нет отзывов' }}
        </div>
      </div>
      <div>
        <div>Маржинальность занятий</div>
        <div>
          {{ stats.client_lesson_counts.payback }}
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.teacher-stats {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 30px;

  &__row {
    & > div {
      &:first-child {
        font-weight: bold;
      }
    }
  }

  &__blocks {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    row-gap: 40px;
    column-gap: 80px;

    & > div {
      display: flex;
      flex-direction: column-reverse;
      justify-content: flex-end;

      & > div {
        &:first-child {
          // font-size: 20px;
          max-width: 250px;
        }
        &:last-child {
          font-weight: bold;
          font-size: 70px;
        }
      }
    }
  }

  &__lessons {
    & > div {
      display: flex;
      & > div {
        &:first-child {
          width: 120px;
        }
      }
    }
  }
}
</style>

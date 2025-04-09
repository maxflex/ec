<script setup lang="ts">
import type { ChartData } from 'chart.js'
import { mdiSchool } from '@mdi/js'
import { Chart, registerables } from 'chart.js'
import { BarChart } from 'vue-chart-3'
import { colors } from '~/plugins/vuetify'
import { categories, options, optionsLessons, type TeacherStatsKey, type TeacherStatsResponse } from '.'

const { teacher } = defineProps<{ teacher: TeacherResource }>()

const loaded = ref(false)
Chart.register(...registerables)

const lessonsChart: ChartData<'bar'> = {
  labels: [],
  datasets: [],
}

const charts: Partial<Record<TeacherStatsKey, ChartData<'bar'>>> = {}

async function loadData() {
  const { data } = await useHttp<TeacherStatsResponse>(
    `teachers/stats/${teacher.id}`,
  )
  const stats = data.value!
  for (const y in stats.avg) {
    const year = y as unknown as Year
    const yearData = stats.avg[year]
    for (const k in yearData) {
      const key = k as TeacherStatsKey
      if (key === 'lessons') {
        continue
      }
      if (!(key in charts)) {
        charts[key] = {
          labels: [],
          datasets: [
            {
              label: 'Среднее',
              data: [],
              backgroundColor: key === 'cancelled_lessons_percent' ? '#ffebee' : colors.border,
            },
            {
              label: formatName(teacher, 'initials'),
              data: [],
              backgroundColor: key === 'cancelled_lessons_percent' ? colors.error : colors.secondary,
            },
          ],
        }
      }
      charts[key]!.labels!.push(year)
      charts[key]!.datasets[0].data.push(yearData[key])

      const teacherValue = (year in stats.teacher)
        ? stats.teacher[year][key]
        : 0
      charts[key]!.datasets[1].data.push(teacherValue)
    }
  }
  const lessons = stats.teacher[currentAcademicYear()].lessons
  lessonsChart.labels = lessons.map(l => DirectionLabel[l.direction])
  lessonsChart.datasets.push({
    label: LessonStatusLabel.conducted,
    data: lessons.map(l => l.conducted),
    backgroundColor: colors.secondary,
  })
  lessonsChart.datasets.push({
    label: LessonStatusLabel.planned,
    data: lessons.map(l => l.planned),
    backgroundColor: colors.border,
  })

  loaded.value = true
}

nextTick(loadData)
</script>

<template>
  <UiLoader v-if="!loaded" />
  <div v-else class="teacher-stats">
    <div class="teacher-stats__category">
      <div class="teacher-stats__category-title">
        <v-icon :icon="mdiSchool" />
        <span>
          Уроки
        </span>
      </div>
      <div class="teacher-stats__chart">
        <BarChart
          :chart-data="lessonsChart"
          :height="300"
          :options="optionsLessons"
        />
      </div>
    </div>
    <div v-for="category in categories" :key="category.key" class="teacher-stats__category">
      <div class="teacher-stats__category-title">
        <v-icon :icon="category.icon" />
        <span>
          {{ category.title }}
        </span>
      </div>
      <div class="teacher-stats__chart">
        <BarChart
          :chart-data="charts[category.key]!"
          :height="300"
          :options="options"
        />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.teacher-stats {
  display: flex;
  flex-direction: column;
  gap: 40px;
  padding: 20px;

  &__category {
    display: flex;
    gap: 20px;
    // background-color: rgb(var(--v-theme-bg));
    background: linear-gradient(to right, #f6f6f6 1%, white);
    // #f6f6f6
    // background-color: #e4e4e4 !important;
    border-radius: 8px;
    overflow: hidden;
    transition: all ease-in-out 0.3s;

    &-title {
      flex: 1;
      position: relative;
      padding: 20px;

      span {
        cursor: default;
        font-size: 26px;
        opacity: 0.9;
      }

      .v-icon {
        position: absolute;
        bottom: -30px;
        left: -30px;
        opacity: 0.1;
        font-size: 160px;
        color: rgb(var(--v-theme-gray));
      }
    }

    // &:hover {
    //   scale: 1.005;
    //   box-shadow:
    //     0 3px 5px -1px var(--v-shadow-key-umbra-opacity, rgba(0, 0, 0, 0.2)),
    //     0 5px 8px 0 var(--v-shadow-key-penumbra-opacity, rgba(0, 0, 0, 0.14)),
    //     0 1px 14px 0 var(--v-shadow-key-ambient-opacity, rgba(0, 0, 0, 0.12)) !important;
    // }
  }

  &__chart {
    padding: 20px;
    width: 900px;
  }
}
</style>

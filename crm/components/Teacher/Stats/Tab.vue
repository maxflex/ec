<script setup lang="ts">
import type { ChartData } from 'chart.js'
import { mdiSchool } from '@mdi/js'
import { Chart, registerables } from 'chart.js'
import { BarChart } from 'vue-chart-3'
import { colors } from '~/plugins/vuetify'
import { categories, extraColors, options, optionsLessons, type TeacherStatsKey, type TeacherStatsResponse } from '.'

const { teacher } = defineProps<{ teacher: TeacherResource }>()
const teacherName = formatName(teacher, 'initials')

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
        // const category = categories.find(e => e.key === key)!
        charts[key] = {
          labels: [],
          datasets: [
            {
              label: 'Среднее',
              data: [],
              backgroundColor: key in extraColors ? extraColors[key]![0] : colors.border,
            },
            {
              label: teacherName,
              data: [],
              backgroundColor: key in extraColors ? extraColors[key]![1] : colors.secondary,
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
  const arr = Array.from({ length: Object.keys(stats.avg).length })
  const lessons = stats.teacher[currentAcademicYear()].lessons
  lessonsChart.labels = arr.map((_, i) => lessons[i] ? DirectionLabel[lessons[i].direction] : '')
  lessonsChart.datasets.push({
    label: LessonStatusLabel.conducted,
    data: arr.map((_, i) => lessons[i] ? lessons[i].conducted : 0),
    backgroundColor: colors.secondary,
    maxBarThickness: 40,
  })
  lessonsChart.datasets.push({
    label: LessonStatusLabel.planned,
    data: arr.map((_, i) => lessons[i] ? lessons[i].planned : 0),
    backgroundColor: colors.border,
    maxBarThickness: 40,
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
        <div>
          Распределение уроков по направлениям. Данные приведены только за текущий учебный год
        </div>
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
          :chart-id="category.key"
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
    border-radius: 16px;
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

      & > div {
        margin-top: 20px;
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

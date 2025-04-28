<script setup lang="ts">
import type { ChartData } from 'chart.js'
import type { TeacherStatsKey, TeacherStatsResponse } from '.'
import { Chart, registerables } from 'chart.js'
import { BarChart } from 'vue-chart-3'
import { colors } from '~/plugins/vuetify'
import { absoluteValues, categories, extraColors, options, optionsLessons } from '.'

const { teacher } = defineProps<{ teacher: TeacherResource }>()

Chart.register(...registerables)

const maxBarThickness = 15
const barPercentage = 2
const categoryPercentage = 0.55

const teacherName = formatName(teacher, 'initials')
const loaded = ref(false)

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
              // @ts-expect-error
              absoluteValues: [],
              backgroundColor: key in extraColors ? extraColors[key]![0] : colors.primary,
              maxBarThickness,
              barPercentage,
              categoryPercentage,
            },
            {
              label: teacherName,
              data: [],
              // @ts-expect-error
              absoluteValues: [],
              backgroundColor: key in extraColors ? extraColors[key]![1] : colors.error,
              maxBarThickness,
              barPercentage,
              categoryPercentage,
            },
          ],
        }
      }
      charts[key]!.labels!.push(year)
      charts[key]!.datasets[0].data.push(yearData[key])

      if (key in absoluteValues) {
        // @ts-expect-error
        charts[key]!.datasets[0].absoluteValues.push(stats.avg[year][absoluteValues[key]])
        // @ts-expect-error
        const teacherValue = (year in stats.teacher) ? stats.teacher[year][absoluteValues[key]] : 0
        // @ts-expect-error
        charts[key]!.datasets[1].absoluteValues.push(teacherValue)
      }

      const teacherValue = (year in stats.teacher) ? stats.teacher[year][key] : 0
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
    maxBarThickness,
  })
  lessonsChart.datasets.push({
    label: LessonStatusLabel.planned,
    data: arr.map((_, i) => lessons[i] ? lessons[i].planned : 0),
    backgroundColor: colors.border,
    maxBarThickness,
  })

  loaded.value = true
}

nextTick(loadData)
</script>

<template>
  <UiLoader v-if="!loaded" />
  <div v-else class="teacher-stats">
    <div class="teacher-stats__category">
      <div class="teacher-stats__title">
        Уроки
      </div>
      <div class="teacher-stats__desc">
        Распределение уроков по направлениям. Данные приведены только за текущий учебный год
      </div>
      <div class="teacher-stats__chart">
        <BarChart
          :chart-data="lessonsChart"
          :height="240"
          :options="optionsLessons"
        />
      </div>
    </div>
    <div v-for="category in categories" :key="category.key" class="teacher-stats__category">
      <div class="teacher-stats__title">
        {{ category.title }}
      </div>
      <div class="teacher-stats__desc">
        {{ category.desc }}
      </div>
      <v-spacer />
      <div class="teacher-stats__chart">
        <BarChart
          :chart-id="category.key"
          :chart-data="charts[category.key]!"
          :height="240"
          :options="options"
        />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.teacher-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);

  row-gap: 40px;
  column-gap: 40px;

  &__category {
    display: flex;
    flex-direction: column;
    padding: 20px;
  }

  &__title {
    cursor: default;
    font-size: 30px;
    opacity: 0.9;
  }

  &__desc {
    width: 75%;
    padding: 20px 0;
  }

  &__chart {
    margin-top: 20px;
  }
}
</style>

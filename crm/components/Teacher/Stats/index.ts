import type { ChartOptions } from 'chart.js'
import { mdiAccountArrowRight, mdiAccountGroup, mdiAccountMultipleOutline, mdiCalendarAlert, mdiCancel, mdiClockTimeEight, mdiCurrencyRub, mdiCurrencyUsd, mdiFileCompare, mdiFileDocumentAlert, mdiFileDocumentEditOutline, mdiHome, mdiHumanMaleBoard, mdiStarCircle, mdiStarCircleOutline, mdiStarShooting } from '@mdi/js'

interface TeacherStats {
  lessons: Array<{
    direction: Direction
    conducted: number
    planned: number
  }>
  cancelled_lessons_percent: number
  report_fill_avg: number
  report_similarity_percent: number
  conducted_next_day_percent: number
  client_reviews_avg: number
  client_lessons_late_percent: number
  client_lessons_online_percent: number
  client_lessons_absent_percent: number
  students_left_percent: number
  payback: number

  client_reviews_count: number
  client_lessons_count: number
  reports_count: number
  conducted_lessons_count: number
}

export type TeacherStatsKey = keyof TeacherStats

type TeacherStatsByYear = Record<Year, TeacherStats>

export interface TeacherStatsResponse {
  teacher: TeacherStatsByYear
  avg: TeacherStatsByYear
}

interface TeacherStatsCategory {
  key: TeacherStatsKey
  icon: string
  title: string
}

export const categories: TeacherStatsCategory[] = [
  {
    key: 'cancelled_lessons_percent',
    icon: mdiCancel,
    title: 'Доля отмен',
  },
  {
    key: 'conducted_lessons_count',
    icon: mdiHumanMaleBoard,
    title: 'Количество проведённых занятий',
  },
  {
    key: 'client_lessons_count',
    icon: mdiAccountGroup,
    title: 'Количество человеко-занятий',
  },
  {
    key: 'conducted_next_day_percent',
    icon: mdiCalendarAlert,
    title: 'Доля занятий, которая была проведена не в день занятия',
  },
  {
    key: 'client_lessons_absent_percent',
    icon: mdiAccountMultipleOutline,
    title: 'Доля отсутствующих учеников в проведенных занятиях',
  },
  {
    key: 'client_lessons_late_percent',
    icon: mdiClockTimeEight,
    title: 'Доля опаздывающих',
  },
  {
    key: 'client_lessons_online_percent',
    icon: mdiHome,
    title: 'Доля дистанционщиков',
  },
  {
    key: 'students_left_percent',
    icon: mdiAccountArrowRight,
    title: 'Доля занятий за счет ушедших учеников',
  },
  {
    key: 'report_similarity_percent',
    icon: mdiFileDocumentAlert,
    title: 'Степень "одинаковости" отчетов',
  },
  {
    key: 'reports_count',
    icon: mdiFileDocumentEditOutline,
    title: 'Количество отчетов',
  },
  {
    key: 'client_reviews_avg',
    icon: mdiStarCircle,
    title: 'Средняя оценка по отзывам',
  },
  {
    key: 'client_reviews_count',
    icon: mdiStarCircleOutline,
    title: 'Количество отзывов',
  },
  {
    key: 'payback',
    icon: mdiCurrencyRub,
    title: 'Маржинальность занятий',
  },
]

export const options: ChartOptions<'bar'> = {
  plugins: {
    legend: {
      display: false,
    },
    title: {
      display: false,
    },
    tooltip: {
      callbacks: {
        label(context: any) {
          const label = context.dataset.label || ''
          const value = context.parsed.y ?? context.parsed

          const title = `${label}: ${value}`
          const percentTitle = `${title}%`

          const index = this.chart.id as unknown as number
          if (index === 0) {
            return title
          }

          const isPercent = categories[index - 1].key.endsWith('_percent')

          return isPercent ? percentTitle : title
        },
        title(tooltipItems) {
          const year = tooltipItems[0].label as unknown as Year
          return YearLabel[year]
        },
      },
    },
  },
  scales: {
    y: {
      ticks: {
        callback(value) {
          const index = this.chart.id as unknown as number
          if (index === 0) {
            return value
          }

          const isPercent = categories[index - 1].key.endsWith('_percent')
          return isPercent ? `${value}%` : value
        },
      },
    },
  },
}

export const optionsLessons: ChartOptions<'bar'> = {
  plugins: {
    legend: {
      display: false,
    },
    title: {
      display: false,
    },
  },
  scales: {
    x: {
      stacked: true,
    },
    y: {
      stacked: true,
      beginAtZero: true,
    },
  },
}

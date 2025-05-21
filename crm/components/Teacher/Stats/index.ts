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
  client_lessons_late_percent: number
  client_lessons_online_percent: number
  client_lessons_absent_percent: number
  students_left_percent: number
  payback: number

  cancelled_lessons_count: number
  client_lessons_count: number
  reports_count: number
  conducted_lessons_count: number
  client_lessons_avg: number
  client_lessons_late_count: number
  client_lessons_online_count: number
  client_lessons_absent_count: number
  conducted_next_day_count: number
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
  desc?: string
}

export const categories: TeacherStatsCategory[] = [
  {
    key: 'cancelled_lessons_percent',
    icon: mdiCancel,
    title: 'Доля отмен',
    desc: 'Отмены уроков преподавателя в сравнении с отменами всех преподавателей',
  },
  {
    key: 'client_lessons_avg',
    icon: mdiAccountGroup,
    title: 'Численность группы',
    desc: 'Среднее число учеников в группе в проведённых занятиях',
  },
  {
    key: 'conducted_next_day_percent',
    icon: mdiCalendarAlert,
    title: 'Опоздания проводки',
    desc: 'Доля занятий, которая была проведена преподавателем не в день занятия, а в последующие дни',
  },
  {
    key: 'client_lessons_absent_percent',
    icon: mdiAccountMultipleOutline,
    title: 'Доля пропуска',
    desc: 'Доля учеников в статусе "не был" в проведённых занятиях',
  },
  {
    key: 'client_lessons_late_percent',
    icon: mdiClockTimeEight,
    title: 'Доля опаздывающих',
    desc: 'Доля учеников в статусе "опоздал" и "опоздал дист" в проведённых занятиях',
  },
  {
    key: 'client_lessons_online_percent',
    icon: mdiHome,
    title: 'Доля дистанционного обучения',
    desc: 'Доля учеников в статусе "был дист" и "опоздал дист" в проведённых занятиях',
  },
  {
    key: 'students_left_percent',
    icon: mdiAccountArrowRight,
    title: 'Ушедшие ученики',
    desc: 'Доля проведённых человекозанятий учитывая ушедших учеников',
  },
  {
    key: 'report_similarity_percent',
    icon: mdiFileDocumentAlert,
    title: 'Одинаковые отчеты',
    desc: 'Степень внутреннего плагиата при написании отчетов',
  },
  {
    key: 'payback',
    icon: mdiCurrencyRub,
    title: 'Маржинальность',
    desc: 'Соотношение стоимости занятий у ученика к оплате занятий преподавателя',
  },
]

export const extraColors: Partial<Record<TeacherStatsKey, [string, string]>> = {
  // cancelled_lessons_percent: ['#ffebee', colors.error],
}

export const absoluteValues: Partial<Record<TeacherStatsKey, TeacherStatsKey>> = {
  cancelled_lessons_percent: 'cancelled_lessons_count',
  client_lessons_late_percent: 'client_lessons_late_count',
  client_lessons_absent_percent: 'client_lessons_absent_count',
  client_lessons_online_percent: 'client_lessons_online_count',
  conducted_next_day_percent: 'conducted_next_day_count',
  report_similarity_percent: 'reports_count',
}

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
          const absolute = context.chart.data?.datasets[context.datasetIndex].absoluteValues?.[context.dataIndex]

          let title = `${label}: ${value}`
          if (this.chart.canvas.id.endsWith('_percent')) {
            title += '%'
          }

          if (absolute) {
            title += ` (${absolute} всего)`
          }

          return title
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
        precision: 0,

        callback(value) {
          return this.chart.canvas.id.endsWith('_percent') ? `${value}%` : value
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

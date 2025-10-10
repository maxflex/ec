import type { ChartOptions } from 'chart.js'
import { mdiAccountArrowRight, mdiAccountGroup, mdiAccountMultipleOutline, mdiCalendarAlert, mdiCancel, mdiClockTimeEight, mdiCurrencyRub, mdiFileDocumentAlert, mdiHome } from '@mdi/js'

export const apiUrl = `teacher-stats-new`

export interface TeacherStatsItem {
  // ОПОЗДАНИЯ ПРОВОДКИ

  /** Количество проведенных занятий */
  lessons_conducted: number

  /** Количество занятий, проведенных с опозданием (не в дату занятия) */
  lessons_conducted_next_day: number

  // ПОСЕЩАЕМОСТЬ

  /** Суммарное количество детей, посетивших занятия в текущую дату (во всех статусах) */
  client_lessons: number

  /** Среднее количество учеников в проведенных занятиях */
  client_lessons_avg: number

  /** Количество пропусков, то есть "не был" */
  client_lessons_absent: number

  /** Количество опозданий, то есть "опоздал"+"опоздал дист." */
  client_lessons_late: number

  /** Количество удаленки, то есть "дист"+"опоздал дист." */
  client_lessons_online: number

  /** Доля пропусков: "не был" / суммарное количество посещений во всех статусах */
  client_lessons_absent_share: number

  /** Доля опозданий: ("опоздал"+"опоздал дист.") / суммарное количество посещений во всех статусах */
  client_lessons_late_share: number

  /** Доля удаленки: ("дист"+"опоздал дист.") / суммарное количество посещений во всех статусах */
  client_lessons_online_share: number

  // УДЕРЖАНИЕ АУДИТОРИИ

  /** Количество детей, начавших заниматься у преподавателя (client_ID*teacher_ID*program*year) */
  retention_new_students: number

  /** Количество детей, прекративших заниматься (по дате последнего занятия в client_lessons) в конфигурации client_ID*teacher_ID*program*year */
  retention_stopped_students: number

  /** Доля накоплением */
  retention_share: number

  // ВЕДОМОСТЬ

  /** Количество занятий, в которых дом.задание НЕ = NULL */
  lessons_with_homework: number

  /** Количество занятий, в которых есть хотя бы 1 прикрепленный файл */
  lessons_with_files: number

  /** Количество выставленных оценок */
  client_lessons_scores: number

  /** Средняя оценка по занятиям */
  client_lessons_scores_avg: number

  /** Количество оставленных комментариев к оценкам */
  client_lessons_score_comments: number

  /** Количество оставленных общих комментариев */
  client_lessons_comments: number

  // ОТЧЕТЫ

  /** Количество отчетов в статусе опубликовано */
  reports_published: number

  /** Количество отчетов в статусе опубликовано без начисления */
  reports_published_no_price: number

  /** Средняя заполняемость отчетов */
  reports_fill_avg: number

  /** Средняя оценка по отчетам */
  reports_grade_avg: number
}

export type TeacherStatsByDate = Record<string, TeacherStatsItem>

export const TeacherStatsModeLabel = {
  day: 'по дням',
  week: 'по неделям',
  month: 'по месяцам',
} as const

export type TeacherStatsMode = keyof typeof TeacherStatsModeLabel

export interface TeacherStats {
  items: TeacherStatsByDate
  totals: TeacherStatsItem
}

export type TeacherStatsField = keyof TeacherStatsItem

export const labels: Partial<Record<TeacherStatsField, string>> = {
  // ОПОЗДАНИЯ ПРОВОДКИ
  lessons_conducted: 'пров.',
  lessons_conducted_next_day: 'опозд.',

  // ПОСЕЩАЕМОСТЬ
  client_lessons: 'посещ.',
  client_lessons_avg: 'сред.уч.',
  client_lessons_absent: 'проп.',
  client_lessons_late: 'опозд.',
  client_lessons_online: 'удал.',
  // client_lessons_absent_share: '%проп.',
  // client_lessons_late_share: '%опозд.',
  // client_lessons_online_share: '%удал.',

  // УДЕРЖАНИЕ АУДИТОРИИ
  retention_new_students: 'новых',
  retention_stopped_students: 'ушедш.',
  // retention_share: '%удерж.',

  // ВЕДОМОСТЬ
  lessons_with_homework: 'дз',
  lessons_with_files: 'файлы',
  client_lessons_scores: 'оцен.',
  client_lessons_scores_avg: 'ср.оц.',
  client_lessons_score_comments: 'комм.оц.',
  client_lessons_comments: 'комм.',

  // ОТЧЁТЫ
  reports_published: 'отч.',
  reports_published_no_price: 'безнач.',
  reports_fill_avg: '%зап.',
  reports_grade_avg: 'оц.отч.',

  // // ОПОЗДАНИЯ ПРОВОДКИ
  // lessons_conducted: 'проведено',
  // lessons_conducted_next_day: 'с опозданием',

  // // ПОСЕЩАЕМОСТЬ
  // client_lessons: 'посещений',
  // client_lessons_avg: 'посещений сред.',
  // client_lessons_absent: 'пропуски',
  // client_lessons_late: 'опоздания',
  // client_lessons_online: 'удалёнка',
  // client_lessons_absent_share: '% пропусков',
  // client_lessons_late_share: '% опозданий',
  // client_lessons_online_share: '% удалёнки',

  // // УДЕРЖАНИЕ АУДИТОРИИ
  // retention_new_students: 'новых',
  // retention_stopped_students: 'ушедших',
  // retention_share: '% удержания',

  // // ВЕДОМОСТЬ
  // lessons_with_homework: 'с дз',
  // lessons_with_files: 'с файлами',
  // client_lessons_scores: 'оценок',
  // client_lessons_scores_avg: 'средн. оценка',
  // client_lessons_score_comments: 'комм. к оценкам',
  // client_lessons_comments: 'комм. общий',

  // // ОТЧЁТЫ
  // reports_published: 'отчётов',
  // reports_published_no_price: 'без начисл.',
  // reports_fill_avg: 'заполн. %',
  // reports_grade_avg: 'оценка отчёта',
}

export const tooltips: Partial<Record<TeacherStatsField, string>> = {
  // ОПОЗДАНИЯ ПРОВОДКИ
  lessons_conducted: 'Количество проведённых занятий всего',
  lessons_conducted_next_day: 'Количество занятий, которые были проведены с опозданием (не в день занятия)',

  // ПОСЕЩАЕМОСТЬ
  client_lessons: 'Суммарное количество посещений',
  client_lessons_avg: 'Среднее количество учеников на одном проведённом занятии',
  client_lessons_absent: 'Количество пропусков («не был»).',
  client_lessons_late: 'Количество опозданий («опоздал» + «опоздал дист.»).',
  client_lessons_online: 'Количество посещений в формате онлайн («дист.» + «опоздал дист.»).',
  client_lessons_absent_share: 'Доля пропусков = пропуски / общее число посещений × 100%.',
  client_lessons_late_share: 'Доля опозданий = опоздания / общее число посещений × 100%.',
  client_lessons_online_share: 'Доля онлайн = онлайн / общее число посещений × 100%.',

  // УДЕРЖАНИЕ АУДИТОРИИ
  retention_new_students: 'Количество учеников, которые впервые пришли к преподавателю в этот день (по программе).',
  retention_stopped_students: 'Количество учеников, которые ушли (последний день посещения).',
  retention_share: 'Доля удержания = активные ученики / общее количество учеников × 100%.',

  // ВЕДОМОСТЬ
  lessons_with_homework: 'Количество занятий, где было задано домашнее задание.',
  lessons_with_files: 'Количество занятий, к которым были прикреплены файлы.',
  client_lessons_scores: 'Количество выставленных оценок по занятиям.',
  client_lessons_scores_avg: 'Средняя оценка по всем выставленным оценкам.',
  client_lessons_score_comments: 'Количество комментариев к отдельным оценкам.',
  client_lessons_comments: 'Количество общих комментариев к занятиям.',

  // ОТЧЁТЫ
  reports_published: 'Количество отчётов со статусом «опубликовано».',
  reports_published_no_price: 'Количество опубликованных отчётов без начисления.',
  reports_fill_avg: 'Средний процент заполненности отчётов.',
  reports_grade_avg: 'Средняя оценка по опубликованным отчётам.',
}

export const grayFields: Partial<Record<TeacherStatsField, TeacherStatsField>> = {
  client_lessons_absent: 'client_lessons_absent_share',
  client_lessons_late: 'client_lessons_late_share',
  client_lessons_online: 'client_lessons_online_share',
}

/**
 * OLD
 */

interface TeacherStatsGraph {
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
export type TeacherStatsKey = keyof TeacherStatsGraph

type TeacherStatsByYear = Record<Year, TeacherStatsGraph>

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

import type { StatsMetric } from './Metrics'
import { mdiChartBar, mdiChartTimeline, mdiTable } from '@mdi/js'
import { endOfWeek, format, getMonth, getYear, isAfter, isSameDay, toDate } from 'date-fns'

export const StatsModeLabel = {
  day: 'по дням',
  week: 'по неделям',
  month: 'по месяцам',
  year: 'по годам',
} as const

export const StatsDisplayLabel = {
  table: 'таблица',
  bar: 'столбцы',
  years: 'сравнение по годам',
}

type StatsMode = keyof typeof StatsModeLabel
export type StatsDisplay = keyof typeof StatsDisplayLabel

export const StatsDisplayIcon: Record<StatsDisplay, string> = {
  table: mdiTable,
  bar: mdiChartBar,
  years: mdiChartTimeline,
}

export interface StatsListResource {
  date: string
  values: number[]
}

export interface StatsApiResponse {
  data: StatsListResource[]
  is_last_page: boolean
  totals: number[]
}

export interface StatsParams {
  metrics: StatsMetric[]
  mode: StatsMode
  date_from: string | null
  date_to: string | null
}

export interface StatsPreset {
  id: number
  name: string
  params: StatsParams
}

export const defaultStatsParams: StatsParams = {
  metrics: [],
  mode: 'day',
  date_from: null,
  date_to: null,
}

export function formatDateMode(date: string, mode: StatsMode, dateTo: string | null) {
  const month = getMonth(date)
  const monthLabel = MonthLabelShort[month + 1 as Month]
  switch (mode) {
    case 'day':
      return format(date, `d ${monthLabel} yyyy`)

    case 'week':
      let end = endOfWeek(date, { weekStartsOn: 1 })
      if (dateTo) {
        if (isAfter(end, dateTo)) {
          end = toDate(dateTo)
        }
      }
      else {
        const today = new Date()
        if (isSameDay(end, today) || isAfter(end, today)) {
          return `${format(date, 'd')} ${monthLabel} – сегодня`
        }
      }

      const endMonth = getMonth(end)
      const endMonthLabel = MonthLabelShort[endMonth + 1 as Month]
      const year = getYear(end) // обычно конец недели — ориентир года

      return month === endMonth
        ? `${format(date, 'd')} – ${format(end, 'd')} ${monthLabel} ${year}`
        : `${format(date, 'd')} ${monthLabel} – ${format(end, 'd')} ${endMonthLabel} ${year}`

    case 'month': return format(date, `${monthLabel} yyyy`)
    case 'year': return format(date, 'yyyy год')
  }
}

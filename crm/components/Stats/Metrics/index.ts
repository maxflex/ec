import AllPaymentsMetric from './AllPaymentsMetric.vue'
import ReportsMetric from './ReportsMetric.vue'
import RequestsMetric from './RequestsMetric.vue'
import TeacherPaymentsMetric from './TeacherPaymentsMetric.vue'

export const MetricComponents: { [key: string]: {
  width: number
  label: string
  filters: object
} } = {
  // @ts-expect-error
  RequestsMetric,
  // @ts-expect-error
  ReportsMetric,
  // @ts-expect-error
  AllPaymentsMetric,
  // @ts-expect-error
  TeacherPaymentsMetric,
}

export const MetricColors = {
  black: 'чёрный',
  gray: 'серый',
  success: 'зелёный',
  error: 'красный',
}

export type MetricColor = keyof typeof MetricColors
export type MetricComponent = keyof typeof MetricComponents

export interface StatsMetric {
  id: number
  metric: MetricComponent
  color: MetricColor
  label: string
  filters: object
}

export interface StatsParams {
  metrics: StatsMetric[]
  mode: StatsMode
  date: string
}

import AllPaymentsMetric from './AllPaymentsMetric.vue'
import ReportsMetric from './ReportsMetric.vue'
import RequestsMetric from './RequestsMetric.vue'
import TeacherPaymentsMetric from './TeacherPaymentsMetric.vue'

export const MetricComponents = {
  RequestsMetric,
  ReportsMetric,
  AllPaymentsMetric,
  TeacherPaymentsMetric,
} as const

export const MetricColors = {
  black: 'чёрный',
  gray: 'серый',
  success: 'зелёный',
  error: 'красный',
}

export type MetricColor = keyof typeof MetricColors
export type MetricComponent = keyof typeof MetricComponents

export interface StatsMetric {
  metric: MetricComponent
  color: MetricColor
  label: string
  filters: object
}

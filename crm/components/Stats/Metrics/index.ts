import AllPaymentsMetric from './AllPaymentsMetric.vue'
import ContractVersionMetric from './ContractVersionMetric.vue'
import LessonMetric from './LessonMetric.vue'
import PassLogMetric from './PassLogMetric.vue'
import ReportMetric from './ReportMetric.vue'
import ReportSumMetric from './ReportSumMetric.vue'
import RequestMetric from './RequestMetric.vue'
import TeacherPaymentMetric from './TeacherPaymentMetric.vue'
import TeacherServiceMetric from './TeacherServiceMetric.vue'

interface MetricComponentParams {
  width: number
  label: string
  filters: object
}

export const MetricComponents = {
  RequestMetric,
  ReportMetric,
  ReportSumMetric,
  AllPaymentsMetric,
  TeacherPaymentMetric,
  TeacherServiceMetric,
  PassLogMetric,
  LessonMetric,
  ContractVersionMetric,

} as unknown as { [key: string]: MetricComponentParams }

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
  date: string | null
}

export interface StatsPreset {
  id: number
  name: string
  params: StatsParams
}

import CallMetric from './CallMetric.vue'
import ClientLessonMetric from './ClientLessonMetric.vue'
import ClientPaymentMetric from './ClientPaymentMetric.vue'
import ContractMetric from './ContractMetric.vue'
import ContractPaymentMetric from './ContractPaymentMetric.vue'
import LessonMetric from './LessonMetric.vue'
import PassMetric from './PassMetric.vue'
import PercentMetric from './PercentMetric.vue'
import ReportMetric from './ReportMetric.vue'
import RequestMetric from './RequestMetric.vue'
import TeacherLessonMetric from './TeacherLessonMetric.vue'
import TeacherPaymentMetric from './TeacherPaymentMetric.vue'
import TeacherServiceMetric from './TeacherServiceMetric.vue'
import TelegramMessageMetric from './TelegramMessageMetric.vue'
import VisitsMetric from './VisitsMetric.vue'
import WebReviewMetric from './WebReviewMetric.vue'

interface MetricComponentParams {
  width: number
  label: string
  filters: object
  special?: boolean
}

const MetricComponentsUnsorted = {
  PercentMetric,
  CallMetric,
  RequestMetric,
  ReportMetric,
  ClientPaymentMetric,
  ContractPaymentMetric,
  ContractMetric,
  TeacherPaymentMetric,
  TeacherServiceMetric,
  PassMetric,
  LessonMetric,
  TeacherLessonMetric,
  ClientLessonMetric,
  TelegramMessageMetric,
  VisitsMetric,
  WebReviewMetric,
} as unknown as { [key: string]: MetricComponentParams }

export const MetricComponents = Object.fromEntries(
  Object.entries(MetricComponentsUnsorted).sort(([, a], [, b]) => {
    if (b.special) {
      return -1
    }
    return a.label.localeCompare(b.label)
  },
  ),
)

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
  date_from: string | null
  date_to: string | null
}

export interface StatsPreset {
  id: number
  name: string
  params: StatsParams
}

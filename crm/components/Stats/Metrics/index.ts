import { colors } from '~/plugins/vuetify'
import CalculatorMetric from './CalculatorMetric.vue'
import CallMetric from './CallMetric.vue'
import ClientLessonMetric from './ClientLessonMetric.vue'
import ContractMetric from './ContractMetric.vue'
import ContractOtherPaymentMetric from './ContractOtherPaymentMetric.vue'
import ContractProgramMetric from './ContractProgramMetric.vue'
import LessonMetric from './LessonMetric.vue'
import PassMetric from './PassMetric.vue'
import OtherPaymentMetric from './OtherPaymentMetric.vue'
import ReportMetric from './ReportMetric.vue'
import RequestMetric from './RequestMetric.vue'
import RequestPassesMetric from './RequestPassesMetric.vue'
import TeacherLessonMetric from './TeacherLessonMetric.vue'
import TeacherOtherPaymentMetric from './TeacherOtherPaymentMetric.vue'
import TeacherServiceMetric from './TeacherServiceMetric.vue'
import TelegramMessageMetric from './TelegramMessageMetric.vue'
import VisitsMetric from './VisitsMetric.vue'
import WebReviewMetric from './WebReviewMetric.vue'

interface MetricComponentParams {
  width: number
  label: string
  filters: object
}

const MetricComponentsUnsorted = {
  CalculatorMetric,
  CallMetric,
  RequestMetric,
  ReportMetric,
  OtherPaymentMetric,
  ContractMetric,
  ContractProgramMetric,
  ContractOtherPaymentMetric,
  TeacherOtherPaymentMetric,
  TeacherServiceMetric,
  PassMetric,
  LessonMetric,
  TeacherLessonMetric,
  ClientLessonMetric,
  TelegramMessageMetric,
  VisitsMetric,
  WebReviewMetric,
  RequestPassesMetric,
} as unknown as { [key: string]: MetricComponentParams }

export const MetricComponents = Object.fromEntries(
  Object.entries(MetricComponentsUnsorted).sort(([, a], [, b]) => {
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

export const MetricColorHex: Record<MetricColor, string> = {
  black: colors.secondary,
  gray: colors.gray,
  success: colors.success,
  error: colors.error,
}

export interface StatsMetric {
  id: number
  metric: MetricComponent
  color: MetricColor
  label: string
  filters: object
  hidden: boolean
}

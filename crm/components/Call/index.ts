export type CallState = 'Appeared' | 'Connected' | 'Disconnected'

export type CallType = 'incoming' | 'outgoing'
export type CallerType = 'newClient' | 'newClientRecruit' | 'oldClient' | 'oldClientRecruit' | 'activeClient' | 'teacher' | 'other'

export const CallerTypeLabel: Record<CallerType, string> = {
  newClient: 'клиент новый',
  newClientRecruit: 'клиент новый привлечение',
  oldClient: 'клиент старый',
  oldClientRecruit: 'клиент старый привлечение',
  activeClient: 'клиент активный',
  teacher: 'преподаватель',
  other: 'другое',
}

export const CallDurationLabel = {
  very_short: 'очень короткие (< 10 сек)',
  short: 'короткие (10-60 сек)',
  medium: 'средние (1–5 мин)',
  long: 'длинные (5–10 мин)',
  very_long: 'очень длинные (> 10 мин)',
  no_conversation: 'без разговора',
} as const

export type CallDurationFilter = keyof typeof CallDurationLabel

export interface CallAonResource extends PhoneResource {
  entity?: PersonResource
  request_id?: number
}

export interface CallLastInteractionResource {
  id: number
  entry_id: string
  user: PersonResource | null
  type: CallType
  is_missed: boolean
  is_missed_callback: boolean
  created_at: string
  finished_at: string
  answered_at: string | null
}

export interface CallListResource {
  id: number
  entry_id: string
  user: PersonResource | null
  type: CallType
  caller_type: CallerType | null
  number: string
  has_recording: boolean
  recording_url: string | null
  is_missed: boolean
  is_missed_callback: boolean
  created_at: string
  finished_at: string
  answered_at: string | null
  aon: CallAonResource | null
}

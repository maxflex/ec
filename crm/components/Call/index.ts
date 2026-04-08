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
  is_missed: boolean
  is_missed_callback: boolean
  created_at: string
  finished_at: string
  answered_at: string | null
  aon: CallAonResource | null
}

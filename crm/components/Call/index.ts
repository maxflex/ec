export type CallState = 'Appeared' | 'Connected' | 'Disconnected'

export type CallType = 'incoming' | 'outgoing'
export type CallerType = 'newClient' | 'oldClient' | 'teacher' | 'other'

export const CallerTypeLabel: Record<CallerType, string> = {
  newClient: 'клиент новый',
  oldClient: 'клиент старый',
  teacher: 'преподаватель',
  other: 'другое',
}

export interface CallAonResource extends PhoneResource {
  entity?: PersonResource
  request_id?: number
}

export interface CallLastInteractionResource {
  id: string
  user: PersonResource | null
  type: CallType
  is_missed: boolean
  is_missed_callback: boolean
  created_at: string
  finished_at: string
  answered_at: string | null
}

export interface CallListResource {
  id: string
  user: PersonResource | null
  type: CallType
  caller_type: CallerType | null
  number: string
  has_recording: boolean
  summary: string | null
  transcript: string | null
  is_missed: boolean
  is_missed_callback: boolean
  created_at: string
  finished_at: string
  answered_at: string | null
  aon: CallAonResource | null
}

export type CallState = 'Appeared' | 'Connected' | 'Disconnected'

export type CallType = 'incoming' | 'outgoing'
export type CallerType = 'newClient' | 'newClientRecruit' | 'oldClient' | 'teacher' | 'other'

export const CallerTypeLabel: Record<CallerType, string> = {
  newClient: 'новый клиент',
  newClientRecruit: 'вербовка клиента',
  oldClient: 'старый клиент',
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
  summary: string | null
  transcript: string | null
  is_missed: boolean
  is_missed_callback: boolean
  created_at: string
  finished_at: string
  answered_at: string | null
  aon: CallAonResource | null
}

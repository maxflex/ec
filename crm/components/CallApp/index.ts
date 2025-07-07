export type CallState = 'Appeared' | 'Connected' | 'Disconnected'

export type CallType = 'incoming' | 'outgoing'

export interface CallAppAonResource extends PhoneResource {
  entity?: PersonResource
  request_id?: number
}

export interface CallEvent {
  state: CallState
  type: CallType
  user?: PersonResource
  aon: CallAppAonResource | null
  last_interaction: CallAppLastInteractionResource | null
  number: string
  answered_at?: string
}

export interface CallAppLastInteractionResource {
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
  number: string
  has_recording: boolean
  is_missed: boolean
  is_missed_callback: boolean
  created_at: string
  finished_at: string
  answered_at: string | null
  aon: CallAppAonResource | null
}

export const isMissed = (ce: CallEvent) => ce.state === 'Disconnected' && !ce.user

export const hasIncoming = ref(false)
export const callAppDialog = ref(false)

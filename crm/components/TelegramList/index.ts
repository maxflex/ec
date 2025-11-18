export interface TelegramListResource {
  id: number
  send_to: SendTo[]
  status: TelegramListStatus
  recipients: Recipients
  scheduled_at?: string
  created_at?: string
  event_id?: number
  event?: {
    id: number
    name: string
  }
  text: string
  user?: PersonResource
  result: Record<SendTo, TelegramListResult[]>
}

export const modelDefaults: TelegramListResource = {
  id: newId(),
  send_to: ['students', 'representatives', 'teachers'],
  text: '',
  status: 'scheduled',
  recipients: {
    students: [],
    representatives: [],
    teachers: [],
  },
  result: {
    students: [],
    teachers: [],
    representatives: [],
  },
}

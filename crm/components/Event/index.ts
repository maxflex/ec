import type { ClientDirection } from '../Client'

export interface EventListResource {
  id: number
  name: string
  date: string
  description: string | null
  telegram_lists_count: number
  time?: string
  file: UploadedFile | null
  time_end?: string
  user: PersonResource
  participants: Record<EventParticipantConfirmation, number>
  participant?: {
    id: number
    confirmation: EventParticipantConfirmation
  }
}

export interface EventParticipant {
  id: number
  confirmation: EventParticipantConfirmation
  is_visited: boolean
  entity: PersonResource
  directions?: ClientDirection[]
}

export interface EventResource {
  id: number
  name: string
  year: Year
  date: string
  time?: string
  duration: number | null
  description: string | null
  user?: PersonWithPhotoResource
  created_at?: string
  file: UploadedFile | null
  telegram_lists: Array<{
    id: number
    created_at: string
  }>
  participants?: {
    clients: EventParticipant[]
    teachers: EventParticipant[]
  }
}

export const modelDefaults: EventResource = {
  id: newId(),
  year: currentAcademicYear(),
  date: today(),
  name: '',
  description: null,
  duration: null,
  telegram_lists: [],
  file: null,
}

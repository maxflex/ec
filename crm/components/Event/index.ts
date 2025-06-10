export interface EventListResource {
  id: number
  name: string
  date: string
  description: string | null
  is_afterclass: boolean
  is_private: boolean
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
  entity: PersonResource
}

export interface EventResource {
  id: number
  name: string
  year: Year
  date: string
  time?: string
  duration: number | null
  description: string | null
  is_afterclass: boolean
  is_private: boolean
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
  is_afterclass: false,
  is_private: false,
  telegram_lists: [],
  file: null,
}

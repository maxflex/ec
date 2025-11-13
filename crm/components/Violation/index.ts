export interface ViolationResource {
  id: number
  is_resolved: boolean
  lesson_id: number
  client_lesson_id: number | null
  comment: string
  file: UploadedFile | null
  user?: PersonResource
  created_at?: string
}

export interface ViolationListResource extends ViolationResource {
  user: PersonResource
  client: PersonResource | null
  group: {
    id: number
    letter: number | null
    program: Program
  }
  lesson: {
    date: string
    time: string
    time_end: string
    teacher: PersonResource
  }
}

export const apiUrl = 'violations'

export const modelDefaults: ViolationResource = {
  id: newId(),
  is_resolved: false,
  lesson_id: newId(),
  client_lesson_id: null,
  comment: '',
  file: null,
}

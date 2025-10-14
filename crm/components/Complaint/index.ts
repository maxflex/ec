export const apiUrl = 'complaints'

export interface ComplaintResource {
  id: number
  client_id: number
  teacher_id?: number
  program?: Program
  text: string
  user?: PersonResource
  year: Year
}

export interface ComplaintListResource {
  id: number
  client: PersonResource
  teacher: PersonResource
  program: Program
  text: string
  comments_count: number
  created_at: string
}

export const modelDefaults: ComplaintResource = {
  id: newId(),
  client_id: -1,
  text: '',
  year: currentAcademicYear(),
}

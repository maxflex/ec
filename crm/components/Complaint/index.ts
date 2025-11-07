export const apiUrl = 'complaints'

export interface ComplaintResource {
  id: number
  client_id: number
  teacher_id?: number
  program?: Program
  text: string
  user?: PersonResource
  is_resolved: boolean
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
  is_resolved: boolean
}

export const modelDefaults: ComplaintResource = {
  id: newId(),
  client_id: -1,
  text: '',
  is_resolved: false,
  year: currentAcademicYear(),
}


export const TeacherComplaintStatusLabel = {
  new: 'новая',
  inProgress: 'в процессе',
  closed: 'закрыто',
} as const

export type TeacherComplaintStatus = keyof typeof TeacherComplaintStatusLabel

export interface TeacherComplaintResource {
  id: number
  status: TeacherComplaintStatus
  text: string
  teacher: PersonResource
  comments_count: number
  created_at: string
}

export const apiUrl = 'teacher-complaints'

export const modelDefaults: TeacherComplaintResource = {
  id: newId(),
  status: 'new',
}

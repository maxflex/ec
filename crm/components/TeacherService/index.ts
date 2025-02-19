export interface TeacherServiceResource {
  id: number
  sum: number
  purpose: string | null
  year: Year
  date: string
  teacher_id?: number
  user?: PersonResource
  teacher?: PersonResource
  created_at?: string
}

export const modelDefaults: TeacherServiceResource = {
  id: newId(),
  year: currentAcademicYear(),
  date: today(),
  purpose: null,
  sum: 0,
}

export const apiUrl = 'teacher-services'

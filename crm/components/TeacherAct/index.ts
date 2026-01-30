import type { TeacherContractData } from '../TeacherContract'

export type TeacherActData = Array<{
  teacher: PersonResource
  groups: number
  price: number
  lessons: number
}>

export interface TeacherActResource {
  id: number
  teacher_id: number
  year: Year
  user?: PersonResource
  date: string
  date_from: string | null
  date_to: string | null
  created_at?: string
  data: TeacherContractData | null
  file: UploadedFile | null
}

export interface TeacherActListResource {
  id: number
  teacher: PersonResource
  date: string
  date_from: string | null
  date_to: string | null
  file: UploadedFile | null
  total: {
    groups: number
    lessons: number
    price: number
  }
}

export const apiUrl = 'teacher-acts'

export const modelDefaults: TeacherActResource = {
  id: newId(),
  year: currentAcademicYear(),
  teacher_id: -1,
  date: today(),
  date_from: null,
  file: null,
  date_to: null,
  data: null,
}

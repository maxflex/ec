export type TeacherContractData = Array<{
  group_id: number
  price: number
  lessons: number
}>

export interface TeacherContractResource {
  id: number
  teacher_id: number
  year: Year
  has_problems: boolean
  user?: PersonResource
  date: string
  date_from: string | null
  date_to: string | null
  created_at?: string
  file: UploadedFile | null
  data: TeacherContractData | null
}

export interface TeacherContractListResource {
  id: number
  teacher: PersonResource
  year: Year
  date: string
  seq: number
  has_problems: boolean
  data: TeacherContractData
  file: UploadedFile | null
  date_from: string | null
  date_to: string | null
  total: {
    groups: number
    lessons: number
    price: number
  }
  is_active: boolean
}

export const apiUrl = 'teacher-contracts'

export const modelDefaults: TeacherContractResource = {
  id: newId(),
  year: currentAcademicYear(),
  teacher_id: -1,
  has_problems: false,
  date: today(),
  date_from: null,
  date_to: null,
  data: null,
  file: null,
}

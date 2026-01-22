export type TeacherContractData = Array<{
  group_id: number
  price: number
  cnt: number
}>

export interface TeacherContractResource {
  id: number
  teacher_id: number
  year: Year
  user?: PersonResource
  date: string
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
  data: TeacherContractData
  is_active: boolean
}

export const apiUrl = 'teacher-contracts'

export const modelDefaults: TeacherContractResource = {
  id: newId(),
  year: currentAcademicYear(),
  teacher_id: -1,
  date: today(),
  data: null,
  file: null,
}

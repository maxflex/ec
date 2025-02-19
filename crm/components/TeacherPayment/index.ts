export interface TeacherPaymentResource {
  id: number
  sum: number
  date: string
  year: Year
  method: TeacherPaymentMethod
  is_confirmed: boolean
  card_number: string | null
  teacher_id?: number
  user?: PersonResource
  teacher?: PersonResource
  created_at?: string
}

export const modelDefaults: TeacherPaymentResource = {
  id: newId(),
  year: currentAcademicYear(),
  date: today(),
  method: 'card',
  card_number: null,
  sum: 0,
  is_confirmed: false,
}

export const apiUrl = 'teacher-payments'

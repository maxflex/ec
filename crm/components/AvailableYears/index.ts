export interface AvailableYearsParams {
  clientId?: number
  teacherId?: number
  mode: 'reports' | 'schedule' | 'grades' | 'groups' | 'teacher-payments' | 'teacher-balance' | 'teacher-services'
}

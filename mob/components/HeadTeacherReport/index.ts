export interface HeadTeacherReportResource {
  id: number
  year: Year
  month: Month
  text: string
  teacher?: PersonResource
  created_at?: string
}

export const modelDefaults: HeadTeacherReportResource = {
  id: newId(),
  text: '',
  month: currentMonth(),
  year: currentAcademicYear(),
}

export const apiUrl = 'head-teacher-reports'

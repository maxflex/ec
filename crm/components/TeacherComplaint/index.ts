
export const TeacherComplaintStatusLabel = {
  new: 'новая',
  inProgress: 'в процессе',
  closed: 'закрыто',
} as const

export const TeacherComplaintRecipientLabel = {
  director: 'директор',
  academicDirector: 'руководитель учебной части',
  office: 'учебная часть',
  maintenance: 'хозяйственная часть',
  teacherSupport: 'специалист по работе с преподавателями',
  accounting: 'бухгалтерия',
} as const

export type TeacherComplaintStatus = keyof typeof TeacherComplaintStatusLabel
export type TeacherComplaintRecipient = keyof typeof TeacherComplaintRecipientLabel

export interface TeacherComplaintResource {
  id: number
  status: TeacherComplaintStatus
  recipient: TeacherComplaintRecipient | null
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

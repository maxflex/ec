import type { CurrentLessonResource } from '../Lesson'

export interface TeacherListResource extends PersonWithPhotoResource {
  status: TeacherStatus
  subjects: TeacherSubject[]
  teeth: Teeth
  is_published: boolean
  created_at: string
}

export interface TeacherResource extends PersonWithPhotoResource {
  phones: PhoneResource[]
  status: TeacherStatus
  subjects: TeacherSubject[]
  is_published: boolean
  is_head_teacher: boolean
  balance_type: TeacherBalanceType
  desc?: string
  photo_desc?: string
  schedule: Teeth | null
  current_lesson: null | CurrentLessonResource
  passport: {
    series: string | null
    number: string | null
    address: string | null
    code: string | null
    issued_by: string | null
  }
  so?: number
  created_at?: string
  user?: PersonResource
}

export const modelDefaults: TeacherResource = {
  id: newId(),
  first_name: null,
  last_name: null,
  middle_name: null,
  photo_url: null,
  phones: [],
  subjects: [],
  is_published: false,
  balance_type: 'normal',
  is_head_teacher: false,
  status: 'active',
  entity_type: EntityTypeValue.teacher,
  passport: {
    series: null,
    number: null,
    code: null,
    address: null,
    issued_by: null,
  },
}

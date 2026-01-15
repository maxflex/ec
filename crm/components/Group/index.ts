interface GroupLessonCounts {
  conducted: number
  conducted_free: number
  planned: number
  planned_free: number
}

export interface GroupListResource {
  id: number
  lessons_planned: number
  teacher_counts: Record<number, number>
  lesson_counts: GroupLessonCounts
  client_groups_count: number
  project_students_count: number
  first_lesson_date?: string
  letter: number | null
  program: Program
  teachers: PersonResource[]
  teeth: Teeth
  zoom: Zoom
  cabinets: string[]
}

export interface GroupResource {
  id: number
  letter?: string
  program?: Program
  year: Year
  teachers: PersonResource[]
  teeth?: Teeth
  created_at?: string
  user?: PersonResource
  contract_date: string | null
  zoom: Zoom
  cabinets: string[]
  lessons_planned: number
  first_lesson_date?: string
  client_groups_count: number
  project_students_count: number
  acts_count: number
  teacher_counts: Record<number, number>
  lesson_counts: GroupLessonCounts
  is_in_contract: boolean
}

export const modelDefaults: GroupResource = {
  id: newId(),
  year: currentAcademicYear(),
  teachers: [],
  is_in_contract: false,
  zoom: {
    id: null,
    password: null,
  },
}

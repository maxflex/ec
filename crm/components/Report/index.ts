export interface RealReport {
  id: number
  year: Year
  status: ReportStatus
  teacher: PersonResource
  client: PersonResource
  program: Program
  to_check_at?: string
  lessons_count: number
  count: number
  price: number | null
  grade: LessonScore | null
  fill: number
  is_read: boolean
  is_required?: number
  created_at: string
}

interface FakeReport {
  id: string
  year: Year
  teacher: PersonResource
  client: PersonResource
  program: Program
  lessons_count: number
  count: number
  is_required: number
}

export type ReportListResource = RealReport | FakeReport

export interface ReportTextFields {
  homework_comment: string
  cognitive_ability_comment: string
  knowledge_level_comment: string
  recommendation_comment: string
}

export interface ReportResource extends ReportTextFields {
  id: number
  year: Year
  status: ReportStatus
  is_read: boolean
  grade: LessonScore | null
  price: number | null
  teacher?: PersonResource
  client?: PersonResource
  program?: Program
  created_at?: string
  ai_text: ReportTextFields | null
  client_lessons: JournalResource[]
  count: number
}

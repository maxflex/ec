export interface RealReport {
  id: number
  year: Year
  status: ReportStatus
  has_ai_comment?: boolean
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

export interface ReportResource {
  id: number
  year: Year
  status: ReportStatus
  is_read: boolean
  grade: LessonScore | null
  price: number | null
  model: string | null
  teacher?: PersonResource
  client?: PersonResource
  program?: Program
  created_at?: string
  comment: string
  ai_comment?: string | null
  client_lessons: JournalResource[]
  count: number
}

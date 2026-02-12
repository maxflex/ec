import { diffWordsWithSpace } from 'diff'

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

export const ReportTextFieldLabel = {
  homework_comment: 'Выполнение домашнего задания',
  cognitive_ability_comment: 'Способность усваивать новый материал',
  knowledge_level_comment: 'Текущий уровень знаний',
  recommendation_comment: 'Рекомендации родителям',
  comment: 'Текст отчета', // новое единое поле
} as const

export type ReportTextFields = Record<keyof typeof ReportTextFieldLabel, string>

export type ReportTextField = keyof ReportTextFields

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
  ai_comment?: string | null
  client_lessons: JournalResource[]
  count: number
}

export function getReportTextFields(item: ReportResource): ReportTextField[] {
  // программы направления school8
  const singleCommentPrograms: Program[] = [
    'engSchool8',
    'bioSchool8',
    'geoSchool8',
    'infSchool8',
    'hisSchool8',
    'litSchool8',
    'mathSchool8',
    'socSchool8',
    'rusSchool8',
    'physSchool8',
    'chemSchool8',
  ]

  // ID, после которого должно отображаться единое поле
  const singleCommentId = 52183

  // новый формат отчета, с единым полем?
  const isSingleComment = !!item.program
    && (item.id < 0 || item.id > singleCommentId)
    && singleCommentPrograms.includes(item.program)

  return isSingleComment
    ? [
        'comment',
      ]
    : [
        'homework_comment',
        'cognitive_ability_comment',
        'knowledge_level_comment',
        'recommendation_comment',
      ]
}

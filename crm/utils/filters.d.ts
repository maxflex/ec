import type { ReportRequirement } from '~/utils/types'

declare global {
  interface ClientPaymentFilters {
    year: Year
    method?: ClientPaymentMethod
  }

  interface RequestFilters {
    status?: RequestStatus
    direction?: Direction
  }

  interface CallAppFilters {
    q: string
    status: CallAppStatusFilter
  }

  interface SwampFilters {
    year: Year
    program?: Program
    status?: SwampFilterStatus
  }

  interface ClientTestFilters {
    year: Year
    program?: Program
    status?: ClientTestStatus
  }

  interface GradeFilters {
    year?: Year
    program?: Program
  }

  interface GroupFilters {
    year: Year
    program: Program[]
    teacher_id?: number
  }

  interface ClientFilters {
    q: string
    year: Year
  }

  interface TeacherFilters {
    q?: string
    status?: TeacherStatus
    subjects: Subject[]
  }

  interface ReportFilters {
    year: Year
    program?: Program
    requirement?: ReportRequirement
    status?: ReportStatus
    teacher_id?: number
  }

  interface TopicFilters {
    year: Year
    is_topic_verified?: boolean
  }

  interface ClientReviewFilters {
    type?: number
    program?: Program
    teacher_id?: number
    rating?: number
  }

  interface ReportTeacherFilters {
    year: Year
    type?: number
  }

  interface TeacherPaymentFilters {
    year: Year
    method?: TeacherPaymentMethod
  }

  interface TelegramMessageFilters {
    status?: number
    template?: TelegramTemplate
  }

  interface WebReviewFilters {
    has_exam_scores?: number
    program: Program[]
  }

  interface ContractVersionFilters {
    year: Year
    company?: Company
    is_active?: number
    direction: Direction[]
  }

  interface UserFilters {
    q?: string
    is_active?: number
  }

  interface YearFilters {
    year: Year
  }

  interface AvailableYearsFilter {
    year?: Year
  }

  interface LogFilters {
    type?: LogType
    table?: string
    row_id?: string
  }

  interface PeopleSelectorFilters {
    mode: 'clients' | 'teachers'
    year: Year
    program: Program[]
    statuses: SwampFilterStatus[]
    group_id?: number
    status?: TeacherStatus
    subjects: Subject[]
  }

  interface TelegramListFilters {
    status?: TelegramListStatus
  }

  interface PassFilters {
    status?: PassStatus
    type?: PassType
  }
}

export {}

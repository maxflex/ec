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
    year: Year
    program?: Program
  }

  interface GroupFilters {
    year: Year
    program?: Program
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
    type?: number
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
    template?: TelegramTemplate
  }

  interface WebReviewFilters {
    is_published?: number
    has_exam_score?: number
  }

  interface ContractVersionFilters {
    year: Year
    company?: Company
    is_active?: number
  }

  interface UserFilters {
    q?: string
    is_active?: number
  }

  interface YearFilters {
    year: Year
  }

  interface LogFilters {
    type?: LogType
    table?: LogTable
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

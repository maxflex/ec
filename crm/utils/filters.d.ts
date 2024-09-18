declare global {
  interface ClientPaymentFilters {
    year: Year
    method?: ClientPaymentMethod
  }

  interface RequestFilters {
    status?: RequestStatus
    program?: Program
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
    quarter?: Quarter
    type?: number
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
    year: Year
    type?: number
  }

  interface TeacherPaymentFilters {
    year: Year
    method?: TeacherPaymentMethod
  }

  interface TelegramMessageFilters {
    template?: TelegramTemplate
    type?: number
  }

  interface WebReviewFilters {
    is_published?: number
    has_exam_score?: number
  }
}

export {}

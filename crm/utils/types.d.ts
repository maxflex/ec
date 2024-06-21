declare global {
  type LogTable = keyof typeof LogTableLabel

  type LogType = keyof typeof LogTypeLabel

  type StatsMode = keyof typeof StatsModeLabel

  type ClientPaymentMethod = keyof typeof ClientPaymentMethodLabel

  type CompanyType = keyof typeof CompanyTypeLabel

  type TeacherPaymentMethod = keyof typeof TeacherPaymentMethodLabel

  type Subject = keyof typeof SubjectLabel

  type Branch = keyof typeof BranchLabel

  type TeacherStatus = keyof typeof TeacherStatusLabel

  type Cabinet = keyof typeof CabinetLabel

  type RequestStatus = keyof typeof RequestStatusLabel

  type Program = keyof typeof ProgramLabel

  type Year = keyof typeof YearLabel

  type EntityString = keyof typeof EntityType

  type LessonStatus = keyof typeof LessonStatusLabel

  type ContractLessonStatus = keyof typeof ContractLessonStatusLabel

  interface Meta {
    current_page: number
    last_page: number
    total: number
  }

  interface ApiResponse<T> {
    data: T
    meta: Meta
  }

  interface PersonResource {
    id: number
    first_name: string | null
    last_name: string | null
    middle_name: string | null
  }

  type PersonWithPhonesResource = PersonResource & HasPhones

  type ResponseErrors = string[]

  interface Paginator {
    page: number
    loading: boolean
    isLastPage: boolean
  }

  type InfiniteScrollSide = 'start' | 'end' | 'both'
  type InfiniteScrollStatus = 'ok' | 'empty' | 'loading' | 'error'
  interface InfiniteScrollCallback {
    side: InfiniteScrollSide
    status: InfiniteScrollStatus
  }

  interface MenuItem {
    to: string
    title: string
    icon: string
  }

  type Menu = MenuItem[]

  interface Zoom {
    id: string
    password: string
  }

  interface HasPhoto {
    photo_url: string | null
  }

  interface HasPhones {
    phones: PhoneListResource[]
  }

  interface User extends PersonResource, HasPhoto {
    id: number
    entity_type: string
    telegram_id: string | null
    number: string // phone number
  }

  interface TokenResponse {
    user: User
    token: string
  }

  interface PhoneListResource {
    id: number
    number: string
    comment: string | null
    is_verified: boolean
  }

  interface RequestResource {
    id?: number
    status: RequestStatus
    program: Program | null
    responsible_user_id: number | null
    comment: string | null
    phones: PhoneListResource[]
    user?: PersonResource
    created_at?: string
  }

  interface RequestListResource {
    id: number
    status: RequestStatus
    program: Program | null
    comment: string | null
    responsible_user: PersonResource | null
    client: PersonResource | null
    phones: PhoneListResource[]
    created_at: string
    comments_count: number
  }

  interface ParentResource extends PersonResource, HasPhones {
    passport_series: string | null
    passport_number: string | null
    passport_address: string | null
    passport_code: string | null
    passport_issued_date: string | null
    passport_issued_by: string | null
    fact_address: string | null
  }

  interface ClientListResource extends PersonResource {
    created_at: string
  }

  interface ClientResource extends PersonResource, HasPhoto, HasPhones {
    branches: Branch[]
    birthdate: string | null
    head_teacher_id: number | null
    parent: ParentResource
    user?: PersonResource
  }

  interface CommentResource {
    id: number
    text: string
    user: PersonResource & HasPhoto
    created_at?: string
  }

  interface ClientPaymentResource {
    id: number
    sum: number
    date: string
    year: Year
    method: ClientPaymentMethod
    company: CompanyType
    entity_type: typeof EntityType.contract | typeof EntityType.client
    entity_id: number
    is_confirmed: boolean
    is_return: boolean
    purpose: string | null
    extra: string | null
    created_at?: string
    user?: PersonResource
  }

  interface ContractProgramResource {
    id: number
    program: Program
    lessons: number
    lessons_planned: number
    price: number
    is_closed: boolean
    contract_version_id: number
  }

  interface ContractPaymentResource {
    id: number
    sum: number
    date: string
    contract_version_id: number
  }

  interface ContractVersionResource {
    id: number
    version: number
    sum?: number
    date: string
    programs: ContractProgramResource[]
    payments: ContractPaymentResource[]
    contract: {
      id: number
      year: Year
      company: CompanyType
      client?: PersonResource
    }
    created_at?: string
    user?: PersonResource
  }

  interface ContractVersionListResource {
    id: number
    date: string
    version: number
    sum: number
    payments_count: number
    programs: ContractProgramResource[]
    contract: {
      id: number
      year: Year
      client: PersonResource
    }
  }

  interface ContractResource {
    id: number
    client?: PersonResource
    client_id: number
    year: Year
    company: CompanyType
    versions: ContractVersionListResource[]
    payments: ClientPaymentResource[]
  }

  interface Zoom {
    id: string
    password: string
  }

  interface GroupListResource {
    id: number
    lessons_count: number
    program: Program
    zoom: Zoom | null
  }

  interface GroupResource {
    id?: number
    program?: Program
    year?: Year
    duration?: number
    is_archived: boolean
    exam_date?: string
    zoom: Zoom
    contracts: ContractResource[]
    created_at?: string
  }

  interface LessonListResource {
    id: number
    teacher: PersonResource | null
    status: LessonStatus
    start_at: string
    cabinet: Cabinet
    topic: string | null
  }

  interface LessonResource {
    id?: number
    teacher_id?: number | null
    group_id?: number
    price?: number
    cabinet?: Cabinet
    start_at?: string
    status: LessonStatus
    topic?: string | null
    conducted_at: string | null
    is_topic_verified: boolean
    is_unplanned: boolean
    user?: PersonResource
    created_at?: string
  }

  interface TeacherListResource extends PersonResource {
    status: TeacherStatus
    subjects: Subject[]
    created_at: string
  }

  interface TeacherResource extends PersonResource, HasPhoto {
    phones: PhoneListResource[]
    status: TeacherStatus
    subjects: Subject[]
    desc?: string
    photo_desc?: string
    passport_series?: string
    passport_number?: string
    passport_address?: string
    passport_code?: string
    passport_issued_by?: string
    so?: number
    created_at?: string
  }

  interface TeacherPaymentResource {
    id: number
    sum: number
    date: string
    year: Year
    method: TeacherPaymentMethod
    purpose: string | null
    teacher_id?: number
    user?: PersonResource
    teacher?: PersonResource
    created_at?: string
  }

  interface BalanceItem {
    comment: string
    sum: number
  }

  interface Balance {
    date: string
    balance: number
    items: BalanceItem[]
  }

  interface UserResource extends PersonResource, HasPhoto {
    phones: PhoneListResource[]
    created_at?: string
  }

  interface ClientReviewResource {
    id: number
    program: Program
    text: string
    rating: number
    client?: PersonResource
    teacher?: PersonResource
    created_at?: string
  }

  interface WebReviewScore {
    id: number
    program?: Program
    score?: number
    max_score?: number
  }

  interface WebReviewResource {
    id: number
    is_published: boolean
    text: string
    signature: string
    rating: number
    scores: WebReviewScore[]
    client?: PersonResource
    created_at?: string
  }

  interface StatsResource {
    date: string
    requests_count: number
    new_contracts_count: number
    new_contracts_sum: number
    new_programs_count: number
    programs_added_count: number
    programs_removed_count: number
    contracts_sum_change: number
  }

  // утилита извлекает тип из emit-функции
  // (извлекает тип второго параметра из emit-функции)
  // TODO: delete?
  type EmitType<T> = T extends (e: any, p: infer P) => any ? P : never

  interface ClientTestResource {
    id: number
    client_id: number
    program: Program
    name: string
    file: string
    minutes: number
    questions: TestQuestions
    answers: TestAnswers | null
    started_at: string | null
    finished_at: string | null
    is_finished: boolean
    questions_count: number
  }

  interface TestResource {
    id: number
    program: Program | null
    name: string
    file: string | null
    minutes: number
    questions: TestQuestions | null
    created_at: string | null
    updated_at: string | null
  }

  interface TestQuestion {
    answer: number | null
    score: number | null
  }

  type TestQuestions = TestQuestion[]

  type TestAnswers = Array<number | undefined | null>

  interface ActiveTest {
    test: ClientTestResource
    seconds: number
  }

  interface MacroResource {
    id: number
    title: string
    text: string
  }

  interface VacationResource {
    id: number
    date: string
  }

  interface ScheduleItem {
    id: number
    date: string
    time: string
    status: LessonStatus
    cabinet?: Cabinet
    group: {
      id: number
      program: Program
    }
    contractLesson?: null | {
      id: number
      price: number
      status: ContractLessonStatus
      minutes_late: null | number
    }
  }

  interface Schedule {
    [key: string]: ScheduleItem[]
  }

  interface LogResource {
    id: number
    type: LogType
    table: LogTable | null
    created_at: string
    entity: PersonResource
    row_id: number | null
    entity_type: typeof EntityType.client | typeof EntityType.teacher | typeof EntityType.user
    data: object
  }
}

export {}

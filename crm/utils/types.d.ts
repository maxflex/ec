import type Metrics from '~/components/Stats/Metrics'
import type { EntityTypeLabel } from '~/utils/labels'

declare global {
  type ErrorCode = typeof ErrorCodeLabel[number]

  type CallAppStatusFilter = keyof typeof CallAppStatusFilterLabel

  type StatsMetric = keyof typeof Metrics

  type SwampFilterStatus = keyof typeof SwampFilterStatusLabel

  type TelegramTemplate = keyof typeof TelegramTemplateLabel

  type Weekday = keyof typeof WeekdayLabel

  type Exam = keyof typeof ExamLabel

  type ClientTestStatus = keyof typeof ClientTestStatusLabel

  type Quarter = keyof typeof QuarterLabel

  type LogTable = keyof typeof LogTableLabel

  type LogType = keyof typeof LogTypeLabel

  type StatsMode = keyof typeof StatsModeLabel

  type ClientPaymentMethod = keyof typeof ClientPaymentMethodLabel

  type Company = keyof typeof CompanyLabel

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

  type ClientLessonStatus = keyof typeof ClientLessonStatusLabel

  type LessonScore = keyof typeof LessonScoreLabel

  type ContractEditMode = 'new-contract' | 'new-version' | 'edit'

  interface ClientLessonScore {
    score: LessonScore
    comment: string | null
  }

  interface Meta {
    current_page: number
    last_page: number
    total: number
  }

  interface ApiResponse<T> {
    data: T
    meta: Meta
  }

  interface IndexPageData {
    loading: boolean
    noData: boolean
  }

  interface PersonResource {
    id: number
    first_name: string | null
    last_name: string | null
    middle_name: string | null
  }

  type PersonWithPhonesResource = PersonResource & HasPhones
  type PersonWithPhotoResource = PersonResource & HasPhoto
  type PersonListResource = PersonWithPhotoResource & {
    entity_id: number
    entity_type: typeof EntityType.teacher | typeof EntityType.client
  }

  type ResponseErrors = string[]

  // type Date = `${Year}-${number}${number}-${number}${number}`
  // type Time = `${number}${number}:${number}${number}:${number}${number}`

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

  interface AuthResource extends PersonResource, HasPhoto {
    id: number
    entity_type: typeof EntityType.client | typeof EntityType.user | typeof EntityType.teacher
    telegram_id: string | null
    is_call_notifications: boolean
    number: string // phone number
  }

  interface TokenResponse {
    user: AuthResource
    token: string
  }

  interface PhoneListResource {
    id: number
    number: string
    comment: string | null
    is_verified: boolean
    telegram_id: number | null
    entity_type: typeof EntityType.client | typeof EntityType.teacher | typeof EntityType.clientParent | typeof EntityType.user
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
    created_at?: string
  }

  interface CommentResource {
    id: number
    text: string
    user: PersonWithPhotoResource
    created_at?: string
  }

  interface ClientPaymentResource {
    id: number
    sum: number
    date: string
    year: Year
    method: ClientPaymentMethod
    company: Company
    client?: PersonResource
    client_id: number
    is_return: boolean
    is_confirmed: boolean
    purpose: string | null
    pko_number: number | null
    card_number: string | null
    created_at?: string
    user?: PersonResource
  }

  interface ContractVersionProgramResource {
    id: number
    program: Program
    lessons_planned: number | string
    prices: ContractVersionProgramPrice[]
    is_closed: boolean
    contract_version_id: number
  }

  interface ContractVersionPaymentResource {
    id: number
    sum: number
    date: string
    contract_version_id: number
  }

  interface ContractVersionProgramPrice {
    id: number
    lessons: number | string
    price: number | string
  }

  interface ContractVersionResource {
    id: number
    version: number
    sum?: number
    date: string
    programs: ContractVersionProgramResource[]
    payments: ContractVersionPaymentResource[]
    contract: {
      id: number
      year: Year
      company: Company
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
    programs_count: number
    is_active: boolean
    contract: {
      id: number
      year: Year
      client: PersonResource
    }
  }

  interface ContractPaymentResource {
    id: number
    contract_id: number
    sum: number
    date: string
    method: ClientPaymentMethod
    is_return: boolean
    is_confirmed: boolean
    pko_number: number | null
    card_number: string | null
    created_at?: string
    user?: PersonResource
  }

  interface ContractResource {
    id: number
    client?: PersonResource
    client_id: number
    year: Year
    company: Company
    versions: ContractVersionListResource[]
    payments: ContractPaymentResource[]
  }

  interface Zoom {
    id: string
    password: string
  }

  interface GroupListResource {
    id: number
    lessons_count: number
    client_groups_count: number
    program: Program
    teachers: PersonResource[]
    zoom: Zoom | null
    teeth: Teeth
  }

  interface GroupResource {
    id: number
    program?: Program
    year: Year
    duration?: number
    zoom: Zoom | null
    teachers: PersonResource[]
    teeth?: Teeth
    created_at?: string
    user?: PersonResource
  }

  interface UploadedFile {
    url?: string
    name: string
    size: number
  }

  interface UploadedFileIcon {
    icon: string
    color: string
  }

  interface LessonResource {
    id: number
    teacher_id?: number | null
    group_id?: number
    group?: {
      id: number
      program: Program
    }
    price?: number
    cabinet?: Cabinet
    // TODO: date и time разве могут быть null?
    date?: string
    time?: string
    status: LessonStatus
    quarter: Quarter | null
    topic?: ?string
    homework?: ?string
    files: UploadedFile[]
    conducted_at: string | null
    is_topic_verified: boolean
    is_unplanned: boolean
    teacher?: PersonResource
    user?: PersonResource
    created_at?: string
  }

  interface LessonListResource {
    id: number
    seq?: number // номер урока по порядку
    teacher: PersonResource
    status: LessonStatus
    date: string
    time: string
    time_end: string
    cabinet: ?Cabinet
    is_unplanned: boolean
    is_first: boolean
    clientLesson?: {
      status: ClientLessonStatus
      scores: ClientLessonScore[]
      minutes_late: number | null
      is_remote: boolean
    }
    group: {
      id: number
      program: Program
      students_count: number
    }
  }

  interface ClientLessonResource {
    id: number
    contract_version_program_id: number
    client: PersonWithPhotoResource
    status: ClientLessonStatus
    is_remote: boolean
    minutes_late: number | null
    scores: ClientLessonScore[]
  }

  interface LessonConductResource {
    id: number
    status: LessonStatus
    conducted_at: string | null
    // внимание: client_lesson или client_group, но структура одинаковая
    students: ClientLessonResource[]
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
    user?: PersonResource
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

  interface TeacherServiceResource {
    id: number
    sum: number
    purpose: string | null
    year: Year
    date: string
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
    is_active: boolean
    is_call_notifications: boolean
    phones: PhoneListResource[]
    created_at?: string
  }

  interface RealClientReviewItem {
    id: number
    rating: number
    program: Program
    teacher: PersonResource
    client: PersonResource
    created_at: string
  }

  interface FakeClientReviewItem {
    id: string
    program: Program
    teacher: PersonResource
    client: PersonResource
  }

  type ClientReviewListResource = RealClientReviewItem | FakeClientReviewItem

  interface ClientReviewResource {
    id: number
    text: string
    rating: number
    user?: PersonResource
    teacher?: PersonResource
    client?: PersonResource
    program?: Program
    created_at?: string
  }

  interface WebReviewResource {
    id: number
    client_id: number
    exam_scores: number[]
    is_published: boolean
    text: string
    signature: string
    rating: number
    client?: PersonResource
    user?: PersonResource
    created_at?: string
  }

  interface StatsListResource {
    date: string
    values: number[]
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
    seconds_left?: number
    questions: TestQuestions
    answers: TestAnswers | null
    started_at: string | null
    finished_at: string | null
    is_finished: boolean
    is_active: boolean
    questions_count: number
    client?: PersonResource
    created_at: string
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
    user?: PersonResource
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
    time_end: string
    status: LessonStatus
    cabinet: Cabinet
    is_unplanned: boolean
    is_first: boolean
    teacher?: PersonResource
    group: {
      id: number
      program: Program
      students_count: number
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
    entity: PersonResource | null
    row_id: number | null
    ip: string
    entity_type:
      | typeof EntityType.client
      | typeof EntityType.teacher
      | typeof EntityType.user
      | null
    data: any
  }

  type SelectItems = Array<{
    title: string
    value: string | number | boolean
  }>

  interface RealReportItem {
    id: number
    year: Year
    is_published: boolean
    is_moderated: boolean
    teacher: PersonResource
    client: PersonResource
    program: Program
    created_at: string
    lessons_count: number
    price: number | null
  }

  interface FakeReportItem {
    id: string
    year: Year
    teacher: PersonResource
    client: PersonResource
    program: Program
    lessons_count: number
  }

  type ReportListResource = RealReportItem | FakeReportItem

  interface ReportResource {
    id: number
    year: Year
    is_published: boolean
    is_moderated: boolean
    homework_comment: string | null
    price: number | null
    teacher?: PersonResource
    client?: PersonResource
    program?: Program
    created_at?: string
    client_lessons: Array<{
      id: number
      status: ClientLessonStatus
      minutes_late: number
      is_remote: boolean
      lesson: {
        id: number
        date: string
        topic: string
      }
    }>
  }

  interface BillingResource {
    id: number
    year: Year
    company: Company
    parent: PersonResource
    payments: Array<{
      id: number
      date: string
      sum: number
      is_return: boolean
    }>
    version: {
      id: number
      date: string
      payments: Array<{
        id: number
        date: string
        sum: number
      }>
    }
  }

  interface EventListResource {
    id: number
    name: string
    date: string
    description: string | null
    is_afterclass: boolean
    participants_count: number
    time?: string
    time_end?: string
    participant?: {
      id: number
      is_confirmed: boolean
    }
  }

  interface EventResource {
    id: number
    name: string
    year: Year
    date: string
    time?: string
    duration: number | null
    description: string | null
    is_afterclass: boolean
    user?: PersonResource
    created_at?: string
  }

  interface EventParticipant {
    id: number
    is_confirmed: boolean
    entity_id: number
    entity_type: typeof EntityType.client | typeof EntityType.teacher
    entity: PersonWithPhotoResource
  }

  interface EventWithParticipantsResource {
    id: number
    participants: EventParticipant[]
  }

  interface TopicListResource {
    id: number
    teacher: PersonResource
    is_topic_verified: boolean
    topic: string
    date: string
    time: string
  }

  interface GroupVisitResource {
    id: number
    dateTime: string
    teacher: PersonResource
    status: LessonStatus
    clientLessons: Array<{
      id: number
      is_remote: boolean
      minutes_late: number
      status: ClientLessonStatus
      client: PersonResource
    }>
  }

  interface RealGradeItem {
    id: number
    client: PersonResource
    program: Program
    year: Year
    quarter: Quarter
    grade: LessonScore
  }

  interface FakeGradeItem {
    id: string
    client: PersonResource
    program: Program
    quarter: Quarter
    year: Year
  }

  type GradeListResource = RealGradeItem | FakeGradeItem

  interface GradeResource {
    id: number
    program?: Program
    quarter?: Quarter
    year?: Year
    teacher?: PersonResource
    client?: PersonResource
    created_at?: string
    grade: LessonScore | null
  }

  interface ExamScoreResource {
    id: number
    web_review_id: number | null
    year: Year
    client_id?: number
    exam?: Exam
    score?: number
    user?: PersonResource
    client?: PersonResource
    created_at?: string
  }

  interface TelegramMessageResource {
    id: number
    entry_id: number
    text: string
    user: PersonWithPhotoResource | null
    template: TelegramTemplate | null
    phone: {
      id: number
      number: string
      entity: PersonWithPhotoResource
    }
    created_at: string
  }

  interface InstructionBaseResource {
    id: number
    is_published: boolean
    title: string
    text: string
    entry_id?: number
  }

  interface InstructionResource extends InstructionBaseResource {
    entry_id: number
    teachers: Array<PersonWithPhotoResource & {
      signed_at: string | null
    }>
    signs: Array<{
      id: number
      teacher: PersonWithPhotoResource
      signed_at: string
    }>
    versions: Array<{
      id: number
      created_at: string
      signs_count: number
    }>
  }

  interface InstructionListResource {
    id: number
    title: string
    is_published: boolean
    versions_count: number
    signs_count: number
    signs_needed: number
    created_at: string
  }

  interface InstructionTeacherListResource {
    id: number
    title: string
    created_at: string
    signed_at: string | null
    is_last_version: boolean
  }

  interface InstructionTeacherResource {
    id: number
    signed_at: string | null
    title: string
    text: string
    is_last_version: boolean
    is_first_version: boolean
    versions: Array<{
      id: number
      created_at: string
      is_last_version: boolean
      signed_at: string | null
    }>
  }

  interface InstructionDiffResource {
    current: {
      title: string
      index: number
    }
    prev: {
      title: string
      index: number
    }
    diff: string
  }

  interface SwampListResource {
    id: number
    total_lessons: number
    total_price: number
    total_price_passed: number
    client: PersonResource
    program: Program
    year: Year
    contract_id: number
    group_id: null | number // group_id = null – ученик не прикреплён к группе по этой программе
  }

  interface Tooth {
    left: number
    width: number
    time: string
    time_end: string
  }

  type Teeth = {
    [key in Weekday]: Tooth[]
  }

  interface ClientGroupResource {
    id: number
    contract_id: number
    teeth: Teeth
    client: PersonWithPhotoResource
  }

  interface MetricItem {
    metric: StatsMetric
    filters: object
  }

  interface ExamDateResource {
    id: number
    exam: Exam
    dates: string[]
  }

  type CallState = 'Appeared' | 'Connected' | 'Disconnected'

  type CallType = 'incoming' | 'outgoing'

  type SseEvent = 'CallEvent' | 'CallSummaryEvent'

  interface CallAppPhoneResource {
    id: number
    comment: ?string
    entity_type: (typeof EntityType)[keyof typeof EntityType]
    person: PersonResource
  }

  interface CallEvent {
    state: CallState
    type: CallType
    user?: PersonResource
    phone: ?CallAppPhoneResource
    number: string
    answered_at?: string
  }

  interface CallListResource {
    id: string
    user: ?PersonResource
    type: CallType
    number: string
    has_recording: boolean
    is_missed: boolean
    is_missed_callback: boolean
    created_at: string
    finished_at: string
    answered_at: ?string
    phone: ?CallAppPhoneResource
  }

  interface ErrorResource {
    id: number
    code: ErrorCode
    entity_id: number
    entity_type: keyof typeof EntityTypeLabel
    number?: string
    person?: PersonResource
  }
}

export {}

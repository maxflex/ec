declare global {
  type Direction = keyof typeof DirectionLabel

  type ReportStatus = keyof typeof ReportStatusLabel

  type PassStatus = keyof typeof PassStatusLabel

  type PassType = keyof typeof PassTypeLabel

  type TelegramListStatus = keyof typeof TelegramListStatusLabel

  type EventParticipantConfirmation = keyof typeof EventParticipantConfirmationLabel

  type SendTo = keyof typeof SendToLabel

  type ErrorCode = typeof ErrorCodeLabel[number]

  type CallAppStatusFilter = keyof typeof CallAppStatusFilterLabel

  type SwampFilterStatus = keyof typeof SwampFilterStatusLabel

  type TelegramTemplate = keyof typeof TelegramTemplateLabel

  type Weekday = keyof typeof WeekdayLabel

  type Exam = keyof typeof ExamLabel

  type ClientTestStatus = keyof typeof ClientTestStatusLabel

  type Quarter = keyof typeof QuarterLabel

  type LogType = keyof typeof LogTypeLabel

  type StatsMode = keyof typeof StatsModeLabel

  type ClientPaymentMethod = keyof typeof ClientPaymentMethodLabel

  type ContractPaymentMethod = keyof typeof ContractPaymentMethodLabel

  type Company = keyof typeof CompanyLabel

  type TeacherPaymentMethod = keyof typeof TeacherPaymentMethodLabel

  type Subject = keyof typeof SubjectLabel

  type Branch = keyof typeof BranchLabel

  type TeacherStatus = keyof typeof TeacherStatusLabel

  type Cabinet = keyof typeof CabinetLabel

  type RequestStatus = keyof typeof RequestStatusLabel

  type Program = keyof typeof ProgramLabel

  type Year = keyof typeof YearLabel

  type Month = keyof typeof MonthLabel

  type EntityString = keyof typeof EntityTypeValue

  type EntityType = keyof typeof EntityTypeLabel

  type LessonStatus = keyof typeof LessonStatusLabel

  type ClientLessonStatus = keyof typeof ClientLessonStatusLabel

  type LessonScore = keyof typeof LessonScoreLabel

  type ContractEditMode = 'new-contract' | 'new-version' | 'edit'

  interface ClientLessonScore {
    score: ?LessonScore
    comment: ?string
  }

  interface Meta {
    current_page: number
    last_page: number
    total: number
  }

  interface ApiResponse<T> {
    data: T[]
    meta: Meta
    extra?: object
  }

  interface IndexPageData {
    loading: boolean
    noData: boolean
  }

  type GlobalMessageColor = 'error' | 'success' | undefined

  type NameFormat = 'last-first' | 'full' | 'initials'

  interface HasPhoto {
    photo_url: ?string
  }

  interface HasPhones {
    phones: PhoneResource[]
  }

  interface HasName {
    first_name: ?string
    last_name: ?string
    middle_name: ?string
  }

  interface PersonResource extends HasName {
    id: number
    entity_type: EntityType
  }

  type PersonWithPhotoResource = PersonResource & HasPhoto

  interface ClientWithContractsResource extends PersonResource {
    contract_versions: ContractVersionResource[]
  }

  type ResponseErrors = string[]

  // type Date = `${Year}-${number}${number}-${number}${number}`
  // type Time = `${number}${number}:${number}${number}:${number}${number}`

  interface MenuCounts {
    reports: number
  }

  interface MenuItem {
    to: string
    title: string
    icon?: string
    hide?: boolean
    count?: keyof MenuCounts
  }

  interface Submenu {
    title: string
    icon: string
    items: MenuItem[]
  }

  type Menu = Array<MenuItem | Submenu>

  interface Zoom {
    id: ?string
    password: ?string
    // url: string
  }

  interface AuthResource extends PersonWithPhotoResource {
    is_call_notifications?: boolean
    is_head_teacher?: boolean
    has_grades?: boolean
  }

  interface RememberUser extends PersonWithPhotoResource {
    number: string
  }

  interface TokenResponse {
    user: AuthResource
    token: string
  }

  interface PhoneResource {
    id: number
    number: string
    comment: ?string
    telegram_id: ?number
    entity_type: EntityType
    entity_id: number
    is_telegram_disabled: boolean
  }

  interface RequestResource {
    id?: number
    status: RequestStatus
    direction: ?Direction
    responsible_user_id: ?number
    yandex_id: ?string
    google_id: ?string
    ip: ?string
    phones: PhoneResource[]
    user?: PersonResource
    client_id: ?number
    created_at?: string
    associated_clients: ClientWithContractsResource[]
    is_verified?: boolean
  }

  interface RequestListResource {
    id: number
    status: RequestStatus
    direction: ?Direction
    responsible_user: ?PersonResource
    client: ?PersonResource
    phones: PhoneResource[]
    created_at: string
    comments_count: number
    passes: PassResource[]
    user_id: ?number
    is_verified: boolean
    associated_clients: PersonResource[]
    associated_requests_count: number
  }

  interface ParentResource extends HasName, HasPhones {
    id: number
    email?: string
    passport: {
      series: ?string
      number: ?string
      address: ?string
      code: ?string
      issued_date: ?string
      issued_by: ?string
      fact_address: ?string
    }
  }

  interface ClientListResource extends PersonResource {
    directions: Direction[]
    created_at: string
  }

  interface ClientResource extends PersonWithPhotoResource, HasPhones {
    branches: Branch[]
    directions: Direction[]
    head_teacher_id: ?number
    head_teacher?: PersonResource
    parent: ParentResource
    is_remote: boolean
    user?: PersonResource
    created_at?: string
    email?: string
    passport: {
      series: ?string
      number: ?string
      birthdate: ?string
    }
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
    purpose: ?string
    pko_number: ?number
    card_number: ?string
    created_at?: string
    user?: PersonResource
  }

  interface ContractVersionProgramResource {
    id: number
    program: Program
    lessons_planned: number | string
    prices: ContractVersionProgramPrice[]
    contract_version_id: number
    lessons_conducted: number
    lessons_to_be_conducted: number
    lessons_total: number
    client_lesson_prices: number[]
    group_id: ?number
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
    seq: number
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
    free_lessons_count?: number
    created_at?: string
    user?: PersonResource
  }

  interface ContractVersionListResource {
    id: number
    date: string
    seq: number
    sum: number
    payments_count: number
    programs_count: number
    directions: Direction[]
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
    method: ContractPaymentMethod
    is_return: boolean
    is_confirmed: boolean
    pko_number: ?number
    card_number: ?string
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

  interface ContractEditPriceResource {
    id: number
    program: Program
    contract: {
      id: number
      company: Company
      year: Year
    }
  }

  interface Zoom {
    id: string
    password: string
  }

  interface GroupListResource {
    id: number
    lessons_planned: number
    lessons: {
      conducted: number
      conducted_free: number
      planned: number
      planned_free: number
    }
    client_groups_count: number
    first_lesson_date?: string
    program: Program
    teachers: PersonResource[]
    teeth: Teeth
    zoom: Zoom
  }

  interface GroupResource {
    id: number
    program?: Program
    year: Year
    teachers: PersonResource[]
    teeth?: Teeth
    created_at?: string
    user?: PersonResource
    zoom: Zoom
    lessons_planned: number
    lessons: {
      conducted: number
      conducted_free: number
      planned: number
      planned_free: number
    }
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
    teacher_id?: number
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
    conducted_at: ?string
    is_topic_verified: boolean
    is_unplanned: boolean
    is_free: boolean
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
    topic: ?string
    is_topic_verified: boolean
    is_need_conduct: boolean
    is_free: boolean
    homework: ?string
    quarter: Quarter | null
    has_files: boolean
    client_lesson?: {
      id: number
      status: ClientLessonStatus
      scores: ClientLessonScore[]
      price: number
      contract_id: number
      minutes_late: ?number
    }
    group: {
      id: number
      program: Program
      students_count: number
      zoom: Zoom
    }
  }

  interface ClientLessonResource {
    id: number
    contract_version_program_id: number
    client: PersonWithPhotoResource
    status: ClientLessonStatus
    minutes_late: ?number
    scores: ClientLessonScore[]
    comment: ?string
    price: number
  }

  interface LessonConductResource {
    id: number
    topic: ?string
    status: LessonStatus
    conducted_at: ?string
    // внимание: client_lesson или client_group, но структура одинаковая
    students: ClientLessonResource[]
  }

  interface TeacherListResource extends HasName {
    status: TeacherStatus
    subjects: Subject[]
    is_published: boolean
    created_at: string
  }

  interface TeacherResource extends PersonWithPhotoResource {
    phones: PhoneResource[]
    status: TeacherStatus
    subjects: Subject[]
    is_published: boolean
    is_head_teacher: boolean
    desc?: string
    photo_desc?: string
    passport: {
      series: ?string
      number: ?string
      address: ?string
      code: ?string
      issued_by: ?string
    }
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
    is_confirmed: boolean
    card_number: ?string
    teacher_id?: number
    user?: PersonResource
    teacher?: PersonResource
    created_at?: string
  }

  interface TeacherServiceResource {
    id: number
    sum: number
    purpose: ?string
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

  interface UserResource extends PersonWithPhotoResource {
    is_active: boolean
    is_call_notifications: boolean
    phones: PhoneResource[]
    created_at?: string
  }

  interface ClientReviewListResource {
    id: number | string
    rating: number
    lessons_count: number
    years: Year[]
    text: string
    program: Program
    teacher: PersonResource
    client: PersonResource
    created_at: string
  }

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
    text: string
    signature: string
    rating: number
    programs: Program[]
    client?: PersonResource
    user?: PersonResource
    created_at?: string
    has_available_exam_scores: boolean
    is_published: boolean
  }

  interface StatsListResource {
    date: string
    values: number[]
  }

  interface StatsApiResponse {
    data: StatsListResource[]
    is_last_page: boolean
    totals: number[]
  }

  // утилита извлекает тип из emit-функции
  // (извлекает тип второго параметра из emit-функции)
  // TODO: delete?
  // type EmitType<T> = T extends (e: any, p: infer P) => any ? P : never

  interface ClientTestResource {
    id: number
    client_id: number
    program: Program
    name: string
    file: UploadedFile
    minutes: number
    seconds_left?: number
    questions: TestQuestion[]
    answers: TestAnswers | null
    started_at: ?string
    finished_at: ?string
    is_finished: boolean
    is_active: boolean
    questions_count: number
    client?: PersonResource
    created_at: string
  }

  interface TestQuestion {
    answer: ?number
    score: ?number
  }

  interface TestResource {
    id: number
    program: Program | null
    name: string
    file: ?UploadedFile
    minutes: number
    questions: TestQuestion[]
    created_at?: string
    updated_at?: string
    user?: PersonResource
  }

  type TestAnswers = Array<number | undefined | null>

  interface ActiveTest {
    test: ClientTestResource
    seconds: number
  }

  interface MacroResource {
    id: number
    title: string
    text_ooo: string
    text_ip: string
  }

  interface VacationResource {
    id: number
    date: string
  }

  interface LogResource {
    id: number
    type: LogType
    table: ?string
    created_at: string
    entity: ?PersonResource
    emulation_user: ?PersonResource
    row_id: ?number
    ip: string
    data: any
  }

  type SelectItems = Array<{
    title: string
    value: string | number | boolean
  }>

  type ReportRequirement = keyof typeof ReportRequirementLabel

  interface RealReport {
    id: number
    year: Year
    status: ReportStatus
    teacher: PersonResource
    client: PersonResource
    program: Program
    to_check_at?: string
    lessons_count: number
    price: ?number
    grade: ?LessonScore
    fill: number
    requirement: ReportRequirement
    created_at: string
  }

  interface FakeReport {
    id: string
    year: Year
    teacher: PersonResource
    client: PersonResource
    program: Program
    lessons_count: number
    requirement: ReportRequirement
  }

  type ReportListResource = RealReport | FakeReport

  interface ReportResource {
    id: number
    year: Year
    status: ReportStatus
    homework_comment?: string
    cognitive_ability_comment?: string
    knowledge_level_comment?: string
    recommendation_comment?: string
    grade: ?LessonScore
    price: ?number
    teacher?: PersonResource
    client?: PersonResource
    program?: Program
    created_at?: string
    client_lessons: JournalResource[]
    count: number
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
    description: ?string
    is_afterclass: boolean
    participants_count: number
    time?: string
    time_end?: string
    participant?: {
      id: number
      confirmation: EventParticipantConfirmation
    }
  }

  interface EventParticipant {
    id: number
    confirmation: EventParticipantConfirmation
    entity: PersonResource
  }

  interface EventResource {
    id: number
    name: string
    year: Year
    date: string
    time?: string
    duration: ?number
    description: ?string
    is_afterclass: boolean
    user?: PersonResource
    created_at?: string
    participants?: {
      clients: EventParticipant[]
      teachers: EventParticipant[]
    }
  }

  interface TopicListResource {
    id: number
    teacher: PersonResource
    is_topic_verified: boolean
    topic: ?string
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
      minutes_late: number
      status: ClientLessonStatus
      client: PersonResource
    }>
  }

  interface QuartersGradesResource {
    id: string
    client: PersonResource
    program: Program
    quarters: {
      [key in Quarter]: {
        last_teacher_id: ?number
        grade: ?GradeResource
        is_grade_needed: boolean
        client_lessons?: Array<{
          id: number
          minutes_late: ?number
          status: ClientLessonStatus
          scores: ClientLessonScore[]
          lesson: {
            id: number
            date: string
            teacher: PersonResource
            topic: string
          }
        }>
      }
    }
  }

  interface GradeResource {
    id: number
    grade: LessonScore
    program: Program
    created_at: string
    teacher?: PersonResource
  }

  interface ExamScoreResource {
    id: number
    web_review_id: ?number
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
    list_id: number
    text: string
    template: TelegramTemplate | null
    entity: PersonResource
    entity_type: string
    number: string
    telegram_id: ?number
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
      signed_at: ?string
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
    signed_at: ?string
    is_last_version: boolean
  }

  interface InstructionTeacherResource {
    id: number
    signed_at: ?string
    title: string
    text: string
    is_last_version: boolean
    is_first_version: boolean
    versions: Array<{
      id: number
      created_at: string
      is_last_version: boolean
      signed_at: ?string
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
    contract_version_program_id: number
    teeth: Teeth
    client: PersonWithPhotoResource
  }

  interface ExamDateResource {
    id: number
    exam: Exam
    programs: Program[]
    dates: {
      date: string
      is_reserve: number
    }[]
  }

  type CallState = 'Appeared' | 'Connected' | 'Disconnected'

  type CallType = 'incoming' | 'outgoing'

  type SseEvent = 'CallEvent'
    | 'CallSummaryEvent'
    | 'TelegramBotAdded'
    | 'ParticipantConfirmationEvent'
    | 'TelegramListSentEvent'
    | 'AppUpdatedEvent'

  interface CallAppAonResource {
    id: number
    comment: ?string
    entity?: PersonResource
    request_id?: number
  }

  interface CallEvent {
    state: CallState
    type: CallType
    user?: PersonResource
    aon: ?CallAppAonResource
    last_interaction: ?CallAppLastInteractionResource
    number: string
    answered_at?: string
  }

  interface CallAppLastInteractionResource {
    id: string
    user: ?PersonResource
    type: CallType
    is_missed: boolean
    is_missed_callback: boolean
    created_at: string
    finished_at: string
    answered_at: ?string
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
    aon: ?CallAppAonResource
  }

  interface ErrorResource {
    id: number
    code: ErrorCode
    entity_id: number
    entity_type: EntityType
    number?: string
    person?: PersonResource
  }

  interface SearchResultResource extends PersonWithPhotoResource, HasPhones {
    entity_type: EntityType
    is_active: boolean
    // у ученика
    contract_versions?: ContractVersionResource[]
    // у препода
    status?: TeacherStatus
    subjects?: Subject[]
    // у заявки
    request?: RequestListResource
  }

  interface PrintOption {
    id: number
    label: string
  }

  interface PeopleSelectorExtra {
    ids: number[]
    group_ids: number[]
  }

  interface SelectedPeople {
    clients: number[]
    teachers: number[]
  }

  interface PeopleResource {
    clients: PersonResource[]
    teachers: PersonResource[]
  }

  interface TelegramListResult {
    id: number
    is_sent: boolean
    is_parent: boolean
    number: string
    is_telegram_disabled?: boolean
  }

  interface TelegramListResource {
    id: number
    send_to: SendTo
    status: TelegramListStatus
    is_confirmable: boolean
    recipients: PeopleResource
    scheduled_at?: string
    created_at?: string
    event_id?: number
    event?: {
      id: number
      name: string
    }
    text: string
    results?: { [key: string]: TelegramListResult[] }
  }

  interface PassResource {
    id: number
    type: PassType
    date: string
    comment: string
    used_at: ?string
    is_expired: boolean
    request_id?: number
    user?: PersonResource
    created_at?: string
    request?: {
      id: number
      direction: Direction
    }
  }

  interface HeadTeacherReportResource {
    id: number
    year: Year
    month: Month
    text: string
    teacher?: PersonResource
    created_at?: string
  }

  interface GroupActResource {
    id: number
    date: string
    date_from: string
    date_to: string
    group_id?: number
    teacher_id?: number
    teacher?: PersonResource
    lessons?: number
    sum?: number
    user?: PersonResource
    created_at?: string
  }

  interface JournalResource {
    id: number
    status: ?ClientLessonStatus
    minutes_late: number
    scores: ClientLessonScore[]
    program: Program
    comment: ?string
    lesson: {
      id: number
      date: string
      topic: string
      homework: string
      quarter: ?Quarter
      teacher: PersonResource
      files: UploadedFile[]
    }
  }

  interface ScholarshipScoreClient {
    client: PersonResource
    avg_score: number
    scores_count: number
    year: Year
    month: Month
  }

  interface ScholarshipScoreTeacher {
    teacher: PersonResource
    scores_count: number
    total: number
    year: Year
    month: Month
  }

  interface ScholarshipScoreResource {
    id?: number
    score?: number
    year: year
    month: Month
    client: PersonResource
    program: Program
    client_id: number
    lessons_count: number
  }

  interface AllPaymentResource {
    id: number
    date: string
    is_confirmed: boolean
    is_return: boolean
    method: ContractPaymentMethod
    year: Year
    purpose: ?string
    company: Company
    contract_id: ?number
    pko_number: ?number
    client: PersonResource
    sum: number
  }
}

export {}

declare global {
  type LogDevice = keyof typeof LogDeviceLabel

  type CvpStatus = keyof typeof CvpStatusLabel

  type Recepient = keyof typeof RecepientLabel

  type InstructionStatus = keyof typeof InstructionStatusLabel

  type Direction = keyof typeof DirectionLabel

  type ReportStatus = keyof typeof ReportStatusLabel

  type PassStatus = keyof typeof PassStatusLabel

  type TelegramListStatus = keyof typeof TelegramListStatusLabel

  type EventParticipantConfirmation = keyof typeof EventParticipantConfirmationLabel

  type SwampFilterStatus = keyof typeof SwampFilterStatusLabel

  type TelegramTemplate = keyof typeof TelegramTemplateLabel

  type Weekday = 0 | 1 | 2 | 3 | 4 | 5 | 6

  type Exam = keyof typeof ExamLabel

  type ClientTestStatus = keyof typeof ClientTestStatusLabel

  type Quarter = keyof typeof QuarterLabel

  type LogType = keyof typeof LogTypeLabel

  type ContractPaymentMethod = keyof typeof ContractPaymentMethodLabel

  type Company = keyof typeof CompanyLabel

  type TeacherPaymentMethod = keyof typeof TeacherPaymentMethodLabel

  type Subject = keyof typeof SubjectLabel

  type Branch = keyof typeof BranchLabel

  type TeacherStatus = keyof typeof TeacherStatusLabel

  type RequestStatus = keyof typeof RequestStatusLabel

  type Program = keyof typeof ProgramLabel

  type Year = keyof typeof YearLabel

  type HeardAboutUs = keyof typeof HeardAboutUsLabel

  type Month = keyof typeof MonthLabel

  type EntityString = keyof typeof EntityTypeValue

  type EntityType = keyof typeof EntityTypeLabel

  type LessonStatus = keyof typeof LessonStatusLabel

  type ClientLessonStatus = keyof typeof ClientLessonStatusLabel

  type LessonScore = keyof typeof LessonScoreLabel

  type ContractEditMode = 'new-contract' | 'new-version' | 'edit'

  type MetricAggregate = keyof typeof MetricAggregateLabel

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

  type NameFormat = 'last-first' | 'full' | 'initials' | 'first-middle'

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

  type ResponseErrors = string[]

  // type Date = `${Year}-${number}${number}-${number}${number}`
  // type Time = `${number}${number}:${number}${number}:${number}${number}`

  type MenuCountsKey = 'reports' | 'requests'

  // number – показывать цифру, boolean – показывать только кружок
  type MenuCounts = Partial<Record<MenuCountsKey, number | boolean>>

  interface MenuItem {
    to: string
    title: string
    icon?: string
    hide?: boolean
    count?: boolean
  }

  interface Submenu {
    title: string
    icon: string
    items: MenuItem[]
    hide?: boolean
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
    client_id?: number // есть только у представителя
  }

  interface RememberUser extends PersonWithPhotoResource {
    number: string
  }

  interface TokenResponse {
    user: AuthResource
    phone: PhoneResource
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

  interface CommentResource {
    id: number
    text: string
    user: PersonWithPhotoResource
    created_at?: string
  }

  interface Zoom {
    id: string
    password: string
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
    cabinet?: string
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
    is_substitute: boolean
    is_violation: number | null
    violation_comment?: ?string
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
    cabinet: ?string
    is_unplanned: boolean
    is_first: boolean
    topic: ?string
    is_substitute: boolean
    is_topic_verified: boolean
    is_need_conduct: boolean
    is_free: boolean
    is_violation: number | null
    violation_comment?: ?string
    homework: ?string
    quarter: Quarter | null
    has_files: boolean
    students_count: number
    project_students_count?: number
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

  // утилита извлекает тип из emit-функции
  // (извлекает тип второго параметра из emit-функции)
  // TODO: delete?
  // type EmitType<T> = T extends (e: any, p: infer P) => any ? P : never

  interface MacroResource {
    id: number
    title: string
    text_ooo: string
    text_ip: string
    text_ano: string
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
    device: LogDevice
    ip: string
    data: any
  }

  type SelectItems = Array<{
    title: string
    value: string | number | boolean
  }>

  interface RealReport {
    id: number
    year: Year
    status: ReportStatus
    teacher: PersonResource
    client: PersonResource
    program: Program
    to_check_at?: string
    lessons_count: number
    count: number
    price: ?number
    grade: ?LessonScore
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

  type ReportListResource = RealReport | FakeReport

  interface ReportResource {
    id: number
    year: Year
    status: ReportStatus
    homework_comment?: string
    cognitive_ability_comment?: string
    knowledge_level_comment?: string
    recommendation_comment?: string
    is_read: boolean
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
    representative: PersonResource
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
    year: Year
    client_id?: number
    exam?: Exam
    score?: number
    user?: PersonResource
    client?: PersonResource
    created_at?: string
    is_published: boolean
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
    status: InstructionStatus
    title: string
    text: string
    entry_id?: number
  }

  interface InstructionResource extends InstructionBaseResource {
    entry_id: number
    teachers: Array<
      PersonWithPhotoResource & {
        signed_at: ?string
      }
    >
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
    status: InstructionStatus
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

  interface Tooth {
    left: number
    width: number
    time: string
    weekday: Weekday
    time_end: string
    is_past: boolean
  }

  type Teeth = Tooth[]

  interface ClientGroupResource {
    id: number
    contract_version_program_id: number
    teeth: Teeth
    client: PersonWithPhotoResource
    // в каком проекте
    project_id: number | null
    // в какую группу перешел
    group_id: number | null
    // реальный group_id
    real_group_id: number | null
    is_removed: boolean
  }

  type SseEvent =
    | 'CallEvent'
    | 'CallSummaryEvent'
    | 'TelegramBotAdded'
    | 'ParticipantConfirmationEvent'
    | 'TelegramListSentEvent'
    | 'AppUpdatedEvent'
    | 'ClientTestUpdatedEvent'
    | 'RequestUpdatedEvent'

  type SendTo = keyof typeof SendToLabel

  interface TelegramListResult extends PersonResource {
    messages: Array<{
      id: number
      telegram_id: ?number
      number: string
    }>
  }

  type RecipientIds = Record<Recepient, number[]>

  type ClientDirections = Partial<Record<Year, Direction[]>>

  interface RecepientPerson extends PersonResource {
    directions?: ClientDirections
    years?: Year[]
  }

  type Recipients = Record<Recepient, RecepientPerson[]>

  interface TelegramListResource {
    id: number
    send_to: SendTo[]
    status: TelegramListStatus
    is_confirmable: boolean
    recipients: Recipients
    scheduled_at?: string
    created_at?: string
    event_id?: number
    event?: {
      id: number
      name: string
    }
    text: string
    user: PersonResource
    result: Record<SendTo, TelegramListResult[]>
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
      time: string
      time_end: string
    }
  }

  interface YearFilters {
    year: Year
  }

  interface ScheduleOverlap {
    count: number
    programs: Program[]
  }

}

export {}

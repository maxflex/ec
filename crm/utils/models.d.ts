export interface ClientPayment {
  // columns
  id: number
  sum: number
  date: string
  year: number
  method: ClientPaymentMethod
  company: CompanyType
  is_confirmed: boolean
  is_return: boolean
  entity_type: string
  entity_id: number
  purpose: string | null
  extra: string | null
  user_id: number
  created_at: string | null
  updated_at: string | null
}
export type ClientPayments = ClientPayment[]

export interface ClientReview {
  // columns
  id: number
  client_id: number
  teacher_id: number
  program: string
  text: string
  rating: unknown
  user_id: number
  created_at: string | null
  updated_at: string | null
}
export type ClientReviews = ClientReview[]

export interface ClientTest {
  // columns
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
  // mutators
  is_finished: boolean
  questions_count: number
}
export type ClientTests = ClientTest[]

export interface Comment {
  // columns
  id: number
  user_id: number | null
  text: string
  entity_type: string
  entity_id: number
  created_at: string | null
  updated_at: string | null
}
export type Comments = Comment[]

export interface Contract {
  // columns
  id: number
  client_id: number
  year: Year
  company: CompanyType
  // relations
  client: Client
  versions: ContractVersions
  groups: Groups
  payments: ClientPayments
}
export type Contracts = Contract[]

export interface ContractGroup {
  // columns
  contract_id: number
  group_id: number
  // relations
  group: Group
}
export type ContractGroups = ContractGroup[]

export interface ContractLesson {
  // columns
  id: number
  contract_id: number
  lesson_id: number
  price: number
  status: ContractLessonStatus
  minutes_late: number | null
  is_remote: boolean
}
export type ContractLessons = ContractLesson[]

export interface ContractPayment {
  // columns
  id: number
  contract_version_id: number
  sum: number
  date: string
}
export type ContractPayments = ContractPayment[]

export interface ContractProgram {
  // columns
  id: number
  contract_version_id: number
  program: Program
  lessons: number
  lessons_planned: number
  price: number
  is_closed: boolean
}
export type ContractPrograms = ContractProgram[]

export interface ContractVersion {
  // columns
  id: number
  user_id: number
  contract_id: number
  version: unknown
  date: string
  sum: number
  created_at: string | null
  updated_at: string | null
  // relations
  programs: ContractPrograms
  payments: ContractPayments
}
export type ContractVersions = ContractVersion[]

export interface Group {
  // columns
  id: number
  teacher_id: number
  program: Program
  year: unknown
  is_archived: boolean
  zoom: Zoom | null
  lessons_planned: unknown | null
  created_at: string | null
  updated_at: string | null
  duration: number | null
  exam_date: string | null
  // relations
  teacher: Teacher
  contracts: Contracts
}
export type Groups = Group[]

export interface Lesson {
  // columns
  id: number
  group_id: number
  teacher_id: number | null
  price: number
  status: LessonStatus
  cabinet: string
  start_at: string
  conducted_at: string | null
  is_unplanned: boolean
  is_topic_verified: boolean
  topic: string | null
  user_id: number | null
  created_at: string | null
  updated_at: string | null
}
export type Lessons = Lesson[]

export interface Log {
  // columns
  id: number
  ip: string | null
  type: LogType
  table: string | null
  row_id: number | null
  data: string[]
  entity_type: string | null
  entity_id: number | null
  created_at: string | null
  updated_at: string | null
  // relations
  user: User
}
export type Logs = Log[]

export interface Macro {
  // columns
  id: number
  title: string
  text: string
}
export type Macros = Macro[]

export interface Phone {
  // columns
  id: number
  number: string
  comment: string | null
  is_verified: boolean
  is_parent: boolean
  entity_type: string
  entity_id: number
  telegram_id: number | null
  // relations
  entity: Phone
}
export type Phones = Phone[]

export interface Request {
  // columns
  id?: number
  client_id: number | null
  responsible_user_id: number | null
  status: RequestStatus
  program: Program | null
  google_id: string | null
  yandex_id: string | null
  ip: string | null
  comment: string | null
  user_id: number | null
  created_at: string | null
  updated_at: string | null
  // relations
  responsible_user: User
  client: Client
  phones: Phones
}
export type Requests = Request[]

export interface Review {
  // columns
  id: number
  client_id: number
  text: string
  signature: string
  rating: unknown
  is_published: boolean
  user_id: number
  created_at: string | null
  updated_at: string | null
}
export type Reviews = Review[]

export interface ReviewScore {
  // columns
  id: number
  review_id: number
  program: string
  score: unknown
  score_max: unknown
}
export type ReviewScores = ReviewScore[]

export interface Teacher {
  // columns
  id: number
  first_name: string | null
  last_name: string | null
  middle_name: string | null
  subjects: unknown | null
  status: string
  desc: string | null
  photo_desc: string | null
  passport_series: string | null
  passport_number: string | null
  passport_address: string | null
  passport_code: string | null
  passport_issued_by: string | null
  so: unknown | null
  created_at: string | null
  updated_at: string | null
}
export type Teachers = Teacher[]

export interface TeacherPayment {
  // columns
  id: number
  sum: number
  date: string
  year: unknown
  method: string
  purpose: string | null
  teacher_id: number
  user_id: number
  created_at: string | null
  updated_at: string | null
}
export type TeacherPayments = TeacherPayment[]

export interface TeacherService {
  // columns
  id: number
  sum: number
  date: string
  year: unknown
  purpose: string | null
  teacher_id: number
  user_id: number
  created_at: string | null
  updated_at: string | null
}
export type TeacherServices = TeacherService[]

export interface Test {
  // columns
  id: number
  program: Program | null
  name: string
  file: string | null
  minutes: number
  questions: TestQuestions | null
  created_at: string | null
  updated_at: string | null
}
export type Tests = Test[]

export interface User {
  // columns
  id: number
  first_name: string | null
  last_name: string | null
  middle_name: string | null
  created_at: string | null
  updated_at: string | null
}
export type Users = User[]

export interface Vacation {
  // columns
  id: number
  date: string
}
export type Vacations = Vacation[]

const ClientPaymentMethod = {
  card: 'card',
  online: 'online',
  cash: 'cash',
  invoice: 'invoice',
} as const

export type ClientPaymentMethod = typeof ClientPaymentMethod[keyof typeof ClientPaymentMethod]

const CompanyType = {
  ip: 'ip',
  ooo: 'ooo',
} as const

export type CompanyType = typeof CompanyType[keyof typeof CompanyType]

const ContractLessonStatus = {
  present: 'present',
  late: 'late',
  absent: 'absent',
} as const

export type ContractLessonStatus = typeof ContractLessonStatus[keyof typeof ContractLessonStatus]

const LogType = {
  create: 'create',
  update: 'update',
  delete: 'delete',
  view: 'view',
  auth: 'auth',
} as const

export type LogType = typeof LogType[keyof typeof LogType]

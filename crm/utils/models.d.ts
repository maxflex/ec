export interface Client {
  // columns
  id: number
  first_name: string|null
  last_name: string|null
  middle_name: string|null
  branches: unknown|null
  birthdate: string|null
  user_id: number|null
  head_teacher_id: number|null
  parent_first_name: string|null
  parent_last_name: string|null
  parent_middle_name: string|null
  passport_series: string|null
  passport_number: string|null
  passport_address: string|null
  passport_code: string|null
  passport_issued_date: string|null
  passport_issued_by: string|null
  fact_address: string|null
  created_at: string|null
  updated_at: string|null
  // relations
  contracts: Contracts
  phones: Phones
}
export type Clients = Client[]

export interface Comment {
  // columns
  id: number
  user_id: number|null
  text: string
  entity_type: string
  entity_id: number
  created_at: string|null
  updated_at: string|null
}
export type Comments = Comment[]

export interface Contract {
  // columns
  id: number
  client_id: number
  grade: string
  year: unknown
  is_ip: boolean
  // relations
  client: Client
  versions: ContractVersions
}
export type Contracts = Contract[]

export interface ContractPayment {
  // columns
  id: number
  contract_version_id: number
  sum: number
  date: string
}
export type ContractPayments = ContractPayment[]

export interface ContractSubject {
  // columns
  id: number
  contract_version_id: number
  subject: string
  lessons: boolean
  lessons_planned: boolean
  price: unknown
  is_closed: boolean
}
export type ContractSubjects = ContractSubject[]

export interface ContractVersion {
  // columns
  id: number
  user_id: number
  contract_id: number
  version: unknown
  date: string
  sum: number
  created_at: string|null
  updated_at: string|null
  // relations
  subjects: ContractSubjects
  payments: ContractPayments
}
export type ContractVersions = ContractVersion[]

export interface Group {
  // columns
  id: number
  teacher_id: number
  subject: string
  grade: string
  year: unknown
  is_archived: boolean
  zoom: string[]|null
  lessons_planned: unknown|null
  created_at: string|null
  updated_at: string|null
  // relations
  teacher: Teacher
}
export type Groups = Group[]

export interface Phone {
  // columns
  id: number
  number: string
  comment: string|null
  is_verified: boolean
  is_parent: boolean
  entity_type: string
  entity_id: number
}
export type Phones = Phone[]

export interface Request {
  // columns
  id: number
  responsible_user_id: number|null
  status: RequestStatus
  grade: string|null
  google_id: string|null
  yandex_id: string|null
  ip: string|null
  comment: string|null
  user_id: number|null
  created_at: string|null
  updated_at: string|null
  // mutators
  clients: unknown
  // relations
  responsible_user: User
  phones: Phones
}
export type Requests = Request[]

export interface Teacher {
  // columns
  id: number
  first_name: string|null
  last_name: string|null
  middle_name: string|null
  subjects: unknown|null
  status: string
  desc: string|null
  photo_desc: string|null
  passport_series: string|null
  passport_number: string|null
  passport_address: string|null
  passport_code: string|null
  passport_issued_by: string|null
  so: unknown|null
  created_at: string|null
  updated_at: string|null
}
export type Teachers = Teacher[]

export interface User {
  // columns
  id: number
  first_name: string|null
  last_name: string|null
  middle_name: string|null
  created_at: string|null
  updated_at: string|null
}
export type Users = User[]

const RequestStatus = {
  new: 'new',
  awaiting: 'awaiting',
  finished: 'finished',
} as const;

export type RequestStatus = typeof RequestStatus[keyof typeof RequestStatus]


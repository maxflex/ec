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
  // mutators
  // overrides
  groups: Groups
  swamps: ContractPrograms
  // relations
  contracts: Contracts
  contract_group: ContractGroups
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
  year: Year
  company: CompanyType
  // relations
  client: Client
  versions: ContractVersions
  groups: Groups
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
  created_at: string|null
  updated_at: string|null
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
  zoom: Zoom|null
  lessons_planned: unknown|null
  created_at: string|null
  updated_at: string|null
  // relations
  teacher: Teacher
  contracts: Contracts
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
  program: Program|null
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

const CompanyType = {
  ip: 'ip',
  ooo: 'ooo',
} as const;

export type CompanyType = typeof CompanyType[keyof typeof CompanyType]

const Program = {
  math9: 'math9',
  phys9: 'phys9',
  chem9: 'chem9',
  bio9: 'bio9',
  inf9: 'inf9',
  rus9: 'rus9',
  lit9: 'lit9',
  soc9: 'soc9',
  his9: 'his9',
  eng9: 'eng9',
  geo9: 'geo9',
  essay9: 'essay9',
  math10: 'math10',
  phys10: 'phys10',
  chem10: 'chem10',
  bio10: 'bio10',
  inf10: 'inf10',
  rus10: 'rus10',
  lit10: 'lit10',
  soc10: 'soc10',
  his10: 'his10',
  eng10: 'eng10',
  math11: 'math11',
  phys11: 'phys11',
  chem11: 'chem11',
  bio11: 'bio11',
  inf11: 'inf11',
  rus11: 'rus11',
  lit11: 'lit11',
  soc11: 'soc11',
  his11: 'his11',
  eng11: 'eng11',
  geo11: 'geo11',
  essay11: 'essay11',
  mathExt: 'mathExt',
  physExt: 'physExt',
  chemExt: 'chemExt',
  bioExt: 'bioExt',
  infExt: 'infExt',
  rusExt: 'rusExt',
  litExt: 'litExt',
  socExt: 'socExt',
  hisExt: 'hisExt',
  engExt: 'engExt',
  geoExt: 'geoExt',
  mathSchool8: 'mathSchool8',
  physSchool8: 'physSchool8',
  chemSchool8: 'chemSchool8',
  bioSchool8: 'bioSchool8',
  infSchool8: 'infSchool8',
  rusSchool8: 'rusSchool8',
  litSchool8: 'litSchool8',
  socSchool8: 'socSchool8',
  hisSchool8: 'hisSchool8',
  engSchool8: 'engSchool8',
  geoSchool8: 'geoSchool8',
  mathSchool9: 'mathSchool9',
  physSchool9: 'physSchool9',
  chemSchool9: 'chemSchool9',
  bioSchool9: 'bioSchool9',
  infSchool9: 'infSchool9',
  rusSchool9: 'rusSchool9',
  litSchool9: 'litSchool9',
  socSchool9: 'socSchool9',
  hisSchool9: 'hisSchool9',
  engSchool9: 'engSchool9',
  geoSchool9: 'geoSchool9',
  mathSchool10: 'mathSchool10',
  physSchool10: 'physSchool10',
  chemSchool10: 'chemSchool10',
  bioSchool10: 'bioSchool10',
  infSchool10: 'infSchool10',
  rusSchool10: 'rusSchool10',
  litSchool10: 'litSchool10',
  socSchool10: 'socSchool10',
  hisSchool10: 'hisSchool10',
  engSchool10: 'engSchool10',
  geoSchool10: 'geoSchool10',
  physSchoolOge: 'physSchoolOge',
  chemSchoolOge: 'chemSchoolOge',
  bioSchoolOge: 'bioSchoolOge',
  infSchoolOge: 'infSchoolOge',
  litSchoolOge: 'litSchoolOge',
  socSchoolOge: 'socSchoolOge',
  hisSchoolOge: 'hisSchoolOge',
  engSchoolOge: 'engSchoolOge',
  mathPracticum: 'mathPracticum',
  physPracticum: 'physPracticum',
  chemPracticum: 'chemPracticum',
  bioPracticum: 'bioPracticum',
  infPracticum: 'infPracticum',
  rusPracticum: 'rusPracticum',
  socPracticum: 'socPracticum',
  hisPracticum: 'hisPracticum',
  engPracticum: 'engPracticum',
  geoPracticum: 'geoPracticum',
  mathBase: 'mathBase',
  mathProf: 'mathProf',
} as const;

export type Program = typeof Program[keyof typeof Program]

const RequestStatus = {
  new: 'new',
  awaiting: 'awaiting',
  finished: 'finished',
} as const;

export type RequestStatus = typeof RequestStatus[keyof typeof RequestStatus]


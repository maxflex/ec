import type { ClientTest } from './models'

export const ProgramLabel = {
  math9: 'математика 9 класс',
  phys9: 'физика 9 класс',
  chem9: 'химия 9 класс',
  bio9: 'биология 9 класс',
  inf9: 'информатика 9 класс',
  rus9: 'русский 9 класс',
  lit9: 'литература 9 класс',
  soc9: 'обществознание 9 класс',
  his9: 'история 9 класс',
  eng9: 'английский 9 класс',
  geo9: 'география 9 класс',
  essay9: 'итоговое сочинение',

  math10: 'математика 9 класс',
  phys10: 'физика 9 класс',
  chem10: 'химия 10 класс',
  bio10: 'биология 10 класс',
  inf10: 'информатика 10 класс',
  rus10: 'русский язык 10 класс',
  lit10: 'литература 10 класс',
  soc10: 'обществознание 10 класс',
  his10: 'история 10 класс',
  eng10: 'английский язык 10 класс',

  math11: 'математика 11 класс',
  phys11: 'физика 11 класс',
  chem11: 'химия 11 класс',
  bio11: 'биология 11 класс',
  inf11: 'информатика 11 класс',
  rus11: 'русский язык 11 класс',
  lit11: 'литература 11 класс',
  soc11: 'обществознанеие 11 класс',
  his11: 'история 11 класс',
  eng11: 'английский язык 11 класс',
  geo11: 'география 11 класс',
  essay11: 'итоговое собеседование',

  mathExt: 'математика экстернат',
  physExt: 'физика экстернат',
  chemExt: 'химия экстернат',
  bioExt: 'биология экстернат',
  infExt: 'информатика экстернат',
  rusExt: 'русский язык экстернат',
  litExt: 'литература экстернат',
  socExt: 'обществознание экстернат',
  hisExt: 'история экстетрнат',
  engExt: 'английский язык экстернат',
  geoExt: 'география экстернат',

  mathSchool8: 'математика школа 8 класс',
  physSchool8: 'физика школа 8 класс',
  chemSchool8: 'химия школа 8 класс',
  bioSchool8: 'биология школа 8 класс',
  infSchool8: 'информатика школа 8 класс',
  rusSchool8: 'русский язык школа 8 класс',
  litSchool8: 'литература школа 8 класс',
  socSchool8: 'обществознание школа 8 класс',
  hisSchool8: 'история школа 8 класс',
  engSchool8: 'английский язык школа 8 класс',
  geoSchool8: 'география школа 8 класс',

  mathSchool9: 'математика школа 9 класс',
  physSchool9: 'физика школа 9 класс',
  chemSchool9: 'химия школа 9 класс',
  bioSchool9: 'биология школа 9 класс',
  infSchool9: 'информатика школа 9 класс',
  rusSchool9: 'русский язык школа 9 класс',
  litSchool9: 'литература школа 9 класс',
  socSchool9: 'обществознание школа 9 класс',
  hisSchool9: 'история школа 9 класс',
  engSchool9: 'английский язык школа 9 класс',
  geoSchool9: 'география школа 9 класс',

  physSchoolOge: 'физика школа ОГЭ',
  chemSchoolOge: 'химия школа ОГЭ',
  bioSchoolOge: 'биология школа ОГЭ',
  infSchoolOge: 'информатика школа ОГЭ',
  litSchoolOge: 'литература школа ОГЭ',
  socSchoolOge: 'обществознание школа ОГЭ',
  hisSchoolOge: 'история школа ОГЭ',
  engSchoolOge: 'английский язык школа ОГЭ',

  mathSchool10: 'математика школа 10 класс',
  physSchool10: 'физика школа 10 класс',
  chemSchool10: 'химия школа 10 класс',
  bioSchool10: 'биология школа 10 класс',
  infSchool10: 'информатика школа 10 класс',
  rusSchool10: 'русский язык школа 10 класс',
  litSchool10: 'литература школа 10 класс',
  socSchool10: 'обществознание школа 10 класс',
  hisSchool10: 'история школа 10 класс',
  engSchool10: 'английский язык школа 10 класс',

  mathSchool11: 'математика школа 11 класс',
  physSchool11: 'физика школа 11 класс',
  chemSchool11: 'химия школа 11 класс',
  bioSchool11: 'биология школа 11 класс',
  infSchool11: 'информатика школа 11 класс',
  rusSchool11: 'русский язык школа 11 класс',
  litSchool11: 'литература школа 11 класс',
  socSchool11: 'обществознание школа 11 класс',
  hisSchool11: 'история школа 11 класс',
  engSchool11: 'английский язык школа 11 класс',

  mathPracticum: 'математика практикум',
  physPracticum: 'физика практикум',
  chemPracticum: 'химия практикум',
  bioPracticum: 'биология практикум',
  infPracticum: 'информатика практикум',
  rusPracticum: 'русский язык практикум',
  socPracticum: 'обществознание практикум',
  hisPracticum: 'история практикум',
  engPracticum: 'английский язык практикум',
  geoPracticum: 'география практикум',

  mathBase: 'математика база',
  mathProf: 'математика профиль',
} as const

export const RequestStatusLabel = {
  new: 'новые',
  awaiting: 'в ожидании',
  finished: 'выполненные',
} as const

export const LessonStatusLabel = {
  planned: 'запланировано',
  conducted: 'проведено',
  cancelled: 'отменено',
} as const

export const EntityType = {
  request: 'App\\Models\\Request',
  client: 'App\\Models\\Client',
  teacher: 'App\\Models\\Teacher',
  user: 'App\\Models\\User',
} as const

export const YearLabel = {
  2024: '2024–2025 уч. г.',
  2023: '2023–2024 уч. г.',
  2022: '2022–2023 уч. г.',
  2021: '2021–2022 уч. г.',
  2020: '2020–2021 уч. г.',
  2019: '2019–2020 уч. г.',
  2018: '2018–2019 уч. г.',
  2017: '2017–2018 уч. г.',
  2016: '2016–2017 уч. г.',
  2015: '2015–2016 уч. г.',
} as const

export const Cabinets = [
  '428', '430', '432', '433', '434', '439', '407', '409', '412', '413', '417', '422', '423', '424',
  '10', '35', '205', '214', '221', '301', '302', '303', '304', '305', '310', '311', '314', '319',
  '320', '321', '322', '507', '809',
] as const

export const TeacherStatusLabel = {
  inactive: 'неактивен',
  active: 'ведет занятия сейчас',
  earlyReserve: 'ранний запас',
  lateReserve: 'поздний запас',
  usedToWork: 'ранее работал',
  interview: 'собеседование',
  closed: 'закрыт',
} as const

export const SubjectLabel = {
  math: 'математика',
  phys: 'физика',
  chem: 'химимя',
  bio: 'биология',
  inf: 'информатика',
  rus: 'русский язык',
  lit: 'литература',
  soc: 'обществознание',
  his: 'история',
  eng: 'английский язык',
  geo: 'география',
  soch: 'сочинение',
} as const

export const SubjectLabelShort = {
  math: 'МАТ',
  phys: 'ФИЗ',
  chem: 'ХИМ',
  bio: 'БИО',
  inf: 'ИНФ',
  rus: 'РУС',
  lit: 'ЛИТ',
  soc: 'ОБЩ',
  his: 'ИСТ',
  eng: 'АНГ',
  geo: 'ГЕО',
  soch: 'СОЧ',
}

export const ClientPaymentMethodLabel = {
  card: 'карта',
  online: 'карта онлайн',
  cash: 'наличные',
  invoice: 'счёт',
}

export const TeacherPaymentMethodLabel = {
  card: 'карта',
  cash: 'наличные',
  invoice: 'счёт',
  mutual: 'взаимозачёт',
}

declare global {
  type Subject = keyof typeof SubjectLabel

  type TeacherStatus = keyof typeof TeacherStatusLabel

  type Cabinet = typeof Cabinets[number]

  type RequestStatus = keyof typeof RequestStatusLabel

  type Program = keyof typeof ProgramLabel

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

  type ResponseErrors = string[]

  interface Paginator {
    page: number
    loading: boolean
    isLastPage: boolean
  }

  type InfiniteScrollSide = 'start' | 'end' | 'both'
  type InfiniteScrollStatus = 'ok' | 'empty' | 'loading' | 'error'
  type InfiniteScrollCallback = {
    side: InfiniteScrollSide
    status: InfiniteScrollStatus
  }

  interface MenuItem {
    to: string
    title: string
    icon: string
  }

  type Menu = MenuItem[]

  interface TestQuestion {
    answer: number | null
    score: number | null
  }

  type TestQuestions = TestQuestion[]

  type TestAnswers = Array<number | undefined | null>

  interface ActiveTest {
    test: ClientTest
    seconds: number
  }

  // https://stackoverflow.com/a/45486495/2274406
  type Year = keyof typeof YearLabel

  interface Zoom {
    id: string
    password: string
  }

  type EntityString = keyof typeof EntityType

  type LessonStatus = keyof typeof LessonStatusLabel

  interface User {
    id: number
    entity_type: string
    telegram_id: string | null
    first_name: string | null
    last_name: string | null
    middle_name: string | null
    number: string
  }

  interface TokenResponse {
    user: User
    token: string
  }

  interface RequestFilters {
    status?: RequestStatus
    program?: Program
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

  interface ClientParentResource extends PersonResource {
    passport_series: string | null
    passport_number: string | null
    passport_address: string | null
    passport_code: string | null
    passport_issued_date: string | null
    passport_issued_by: string | null
    fact_address: string | null
    phones: PhoneListResource[]
  }

  interface ClientResource extends PersonResource {
    branches: string[] | null
    birthdate: string | null
    user_id: number | null
    head_teacher_id: number | null
    parent: ClientParentResource
    phones: PhoneListResource[]
    head_teacher: PersonResource | null
  }

  interface CommentResource {
    id: number
    text: string
    user: PersonResource
    created_at?: string
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
    zoom: Zoom
  }

  interface GroupFilters {
    program?: Program
    year?: Year
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
    price?: number
    cabinet?: Cabinet
    start_at?: string
    status: LessonStatus
    topic?: string | null
    conducted_at: string | null
    is_topic_verified: boolean
    is_unplanned: boolean
  }

  interface TeacherResource extends PersonResource {
    phones: PhoneListResource[]
    status: TeacherStatus
  }
}

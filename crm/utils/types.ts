import type { YEARS } from './sment'
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
  geoSchool10: 'география школа 10 класс',

  physSchoolOge: 'физика школа ОГЭ',
  chemSchoolOge: 'химия школа ОГЭ',
  bioSchoolOge: 'биология школа ОГЭ',
  infSchoolOge: 'информатика школа ОГЭ',
  litSchoolOge: 'литература школа ОГЭ',
  socSchoolOge: 'обществознание школа ОГЭ',
  hisSchoolOge: 'история школа ОГЭ',
  engSchoolOge: 'английский язык школа ОГЭ',

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

declare global {
  type RequestStatus = keyof typeof RequestStatusLabel

  interface Meta {
    current_page: number
    last_page: number
    total: number
  }

  interface ApiResponse<T> {
    data: T
    meta: Meta
  }

  interface Person {
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

  export type Program = keyof typeof ProgramLabel
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
  type Year = (typeof YEARS)[number]

  interface Zoom {
    id: string
    password: string
  }

  type EntityString = 'admin' | 'teacher' | 'client'

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

  interface RequestResource {
    id?: number
    status: RequestStatus
    program: Program | null
    responsible_user_id: number | null
    comment: string | null
  }

  interface PhoneListResource {
    id: number
    number: string
    comment: string | null
    is_verified: boolean
  }

  interface RequestListResource {
    id: number
    status: RequestStatus
    program: Program | null
    comment: string | null
    responsible_user: Person | null
    client: Person | null
    phones: PhoneListResource[]
    created_at: string
  }
}

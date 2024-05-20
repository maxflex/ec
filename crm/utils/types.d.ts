import type { YEARS } from './sment'
import type { ClientTest, Program, RequestStatus } from './models'

declare global {
  interface Meta {
    current_page: number
    last_page: number
    total: number
  }

  interface ApiResponse<T> {
    data: T
    meta: Meta
  }

  type NullableString = string | null

  interface Person {
    first_name: NullableString
    last_name: NullableString
    middle_name: NullableString
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
  type Year = (typeof YEARS)[number]

  interface Zoom {
    id: string
    password: string
  }

  type EntityString = 'admin' | 'teacher' | 'client'

  interface User {
    id: number
    entity_type: string
    telegram_id: NullableString
    first_name: NullableString
    last_name: NullableString
    middle_name: NullableString
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
}

export {}

import { YEARS } from "./sment"

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

  interface Paginator {
    page: number
    loading: boolean
    isLastPage: boolean
  }

  type InfiniteScrollSide = "start" | "end" | "both"
  type InfiniteScrollStatus = "ok" | "empty" | "loading" | "error"
  type InfiniteScrollCallback = {
    side: InfiniteScrollSide
    status: InfiniteScrollStatus
  }

  // https://stackoverflow.com/a/45486495/2274406
  type Year = (typeof YEARS)[number]
}

export {}

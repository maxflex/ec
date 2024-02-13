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

interface Meta {
  current_page: number
  last_page: number
}

interface ApiResponse<T> {
  data: T
  meta: Meta
}

export interface ClientReviewListResource {
  id: number | string
  rating: number
  lessons_count: number
  years: Year[]
  text: string
  program: Program
  teacher: PersonResource
  client: PersonResource
  created_at: string
}

export interface ClientReviewResource {
  id: number
  text: string
  rating: number
  user?: PersonResource
  teacher?: PersonResource
  client?: PersonResource
  program?: Program
  created_at?: string
}

export const modelDefaults: ClientReviewResource = {
  id: newId(),
  text: '',
  rating: 0,
}

export const apiUrl = 'client-reviews'

export const apiUrl = 'client-reviews'

export interface ClientReviewResource {
  id: number
  client_id: number
  teacher_id?: number
  program?: Program
  rating?: number
  text: string
  user?: PersonResource
}

export interface ClientReviewListResource {
  id: number
  client: PersonResource
  teacher: PersonResource
  program: Program
  text: string
  rating: number
  created_at: string
}

export const modelDefaults: ClientReviewResource = {
  id: newId(),
  client_id: -1,
  text: '',
}

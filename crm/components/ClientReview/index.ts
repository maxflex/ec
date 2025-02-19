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
  exam_scores: Array<{
    id: number
    exam: Exam
    score: number
    max_score: number
  }>
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
  is_marked: boolean
}

export const modelDefaults: ClientReviewResource = {
  id: newId(),
  text: '',
  rating: 0,
  is_marked: false,
}

export const apiUrl = 'client-reviews'

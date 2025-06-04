export interface WebReviewResource {
  id: number
  client_id: number
  exam_scores: Array<{
    id: number
    exam: Exam
    score: number
    is_published: boolean
  }>
  text: string
  signature: string
  rating: number
  programs: Program[]
  client?: PersonResource
  user?: PersonResource
  created_at?: string
  is_published: boolean
  has_photo?: boolean
}

export const modelDefaults: WebReviewResource = {
  id: newId(),
  client_id: newId(),
  text: '',
  exam_scores: [],
  programs: [],
  signature: '',
  rating: 0,
  is_published: false,
}

export const apiUrl = 'web-reviews'

import type { TestAnswers, TestQuestion } from '../Test'

export interface ClientTestResource {
  id: number
  client_id: number
  name: string
  description: string | null
  file: UploadedFile
  minutes: number
  seconds_left?: number
  questions: TestQuestion[]
  answers: TestAnswers
  started_at: string | null
  finished_at: string | null
  is_finished: boolean
  is_active: boolean
  question_counts: number[]
  client: PersonResource
  created_at: string
  results?: {
    total: number
    score: number
    answers: Record<number, {
      total: number
      score: number
    }>
  }
}

export interface TestQuestion {
  answers: number[]
  score: number | null
}

export type TestAnswers = Record<number, number[]>

export interface TestResource {
  id: number
  name: string
  max_score: number
  description: string | null
  file: UploadedFile | null
  minutes: number
  questions: TestQuestion[]
  created_at?: string
  updated_at?: string
  user?: PersonResource
}

export const modelDefaults: TestResource = {
  id: newId(),
  minutes: 30,
  max_score: -1,
  name: '',
  description: null,
  file: null,
  questions: [],
}

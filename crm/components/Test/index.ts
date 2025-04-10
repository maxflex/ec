import type { ClientTestResource } from '../ClientTest'

export interface TestQuestion {
  answers: number[]
  score: number | null
}

export type TestAnswers = Record<number, number[]>

export interface ActiveTest {
  test: ClientTestResource
  seconds: number
}

export interface TestResource {
  id: number
  program: Program | null
  name: string
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
  name: '',
  file: null,
  program: null,
  questions: [],
}

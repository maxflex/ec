import type { ContractPaymentResource } from '~/components/ContractPayment'

export interface ContractVersionProgramResource {
  id: number
  program: Program
  lessons_planned: number | string
  prices: ContractVersionProgramPrice[]
  contract_version_id: number
  lessons_conducted: number
  lessons_to_be_conducted: number
  lessons_suggest: number
  client_lesson_prices: number[]
  group_id: number | null
}

export interface ContractVersionPaymentResource {
  id: number
  sum: number
  date: string
  contract_version_id: number
}

export interface ContractVersionProgramPrice {
  id: number
  lessons: number | string
  price: number | string
}

export interface ContractVersionResource {
  id: number
  seq: number
  sum?: number
  date: string
  programs: ContractVersionProgramResource[]
  // оригинальные программы (используется когда подгружаем проект договора)
  programs_original: ContractVersionProgramResource[]
  payments: ContractVersionPaymentResource[]
  contract: {
    id: number
    year: Year
    company: Company
    client?: PersonResource
    source: string | null
  }
  free_lessons_count?: number
  created_at?: string
  user?: PersonResource
}

type DirectionCounts = Partial<Record<Direction, number>>

export interface ContractVersionListResource {
  id: number
  date: string
  seq: number
  sum: number
  sum_change: number
  payments_count: number
  programs_count: number
  direction_counts: DirectionCounts
  is_active: boolean
  created_at: string
  contract: {
    id: number
    year: Year
    client: PersonResource
    company: Company
    source: string | null
  }
}

export interface ContractResource {
  id: number
  client?: PersonResource
  client_id: number
  year: Year
  company: Company
  balances?: {
    to_pay: number
    remainder: number
  }
  versions: ContractVersionListResource[]
  payments: ContractPaymentResource[]
}

export interface ContractEditPriceResource {
  id: number
  program: Program
  contract: {
    id: number
    company: Company
    year: Year
  }
}

export const modelDefaults: ContractVersionResource = {
  id: newId(),
  seq: 1,
  date: today(),
  programs: [],
  payments: [],
  contract: {
    id: newId(),
    year: currentAcademicYear(),
    company: null,
    source: null,
  },
}

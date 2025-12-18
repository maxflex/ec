export const OtherPaymentMethodLabel = {
  card: 'карта',
  cash: 'наличные',
  sbp: 'СБП',
} as const

export type OtherPaymentMethod = keyof typeof OtherPaymentMethodLabel

export interface OtherPaymentResource {
  id: number
  sum?: number
  date: string
  first_name: string
  last_name: string
  middle_name: string
  method: OtherPaymentMethod
  is_return: boolean
  is_confirmed: boolean
  purpose: string | null
  pko_number: number | null
  card_number: string | null
  created_at?: string
  user?: PersonResource
}

export interface AllPaymentsResource {
  id: number
  date: string
  first_name: string
  last_name: string
  middle_name: string
  is_confirmed: boolean
  is_return: boolean
  method: ContractPaymentMethod
  year: Year
  purpose: string | null
  company: Company
  contract_id: number | null
  pko_number: number | null
  client_id: number | null
  is_1c_synced: boolean | null
  receipt_number: string | null
  sum: number
}

export const apiUrl = 'other-payments'

export const modelDefaults: OtherPaymentResource = {
  id: newId(),
  date: today(),
  method: 'cash',
  first_name: '',
  last_name: '',
  middle_name: '',
  purpose: '',
  is_confirmed: false,
  is_return: false,
  pko_number: null,
  card_number: null,
}

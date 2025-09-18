import type { PrintOption } from '../Print'

export interface ContractPaymentResource {
  id: number
  contract_id: number
  sum: number
  date: string
  method: ContractPaymentMethod
  is_return: boolean
  is_confirmed: boolean
  pko_number: number | null
  card_number: string | null
  created_at?: string
  user?: PersonResource
}

export const modelDefaults: ContractPaymentResource = {
  id: newId(),
  sum: 0,
  date: today(),
  method: 'card',
  is_confirmed: false,
  is_return: false,
  contract_id: newId(),
  pko_number: null,
  card_number: null,
}

export const printOptions: PrintOption[] = [
  { id: 10, label: 'счёт на оплату' },
  { id: 11, label: 'счёт на оплату (с печатью)' },
  { id: 9, label: 'платежка (наличные)' },
  { id: 14, label: 'платежка НДС (наличные)' },
]

export const apiUrl = 'contract-payments'

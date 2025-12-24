import type { PrintOption } from '../Print'

export interface ContractPaymentResource {
  id: number
  contract_id: number
  sum: number
  date: string
  method: ContractPaymentMethod
  contract: {
    id: number
    company: Company
  }
  is_return: boolean
  is_confirmed: boolean
  is_1c_synced: boolean
  pko_number: number | null
  receipt_number?: string
  receipt_ip?: string
  card_number: string | null
  created_at?: string
  user?: PersonResource
  external_id?: string
  purpose?: string // только у альфа-платежей
}

export const modelDefaults: ContractPaymentResource = {
  id: newId(),
  // @ts-expect-error
  sum: '',
  date: today(),
  method: 'card',
  is_confirmed: false,
  is_return: false,
  is_1c_synced: false,
  contract_id: newId(),
  pko_number: null,
  card_number: null,
  contract: {
    id: newId(),
    company: 'ip',
  },
}

export const printOptions: PrintOption[] = [
  { id: 10, label: 'счёт на оплату' },
  { id: 11, label: 'счёт на оплату (с печатью)' },
  { id: 9, label: 'платежка (наличные)' },
  { id: 14, label: 'платежка НДС (наличные)' },
]

export const apiUrl = 'contract-payments'

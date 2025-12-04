import type { ContractPaymentResource } from '../ContractPayment'

export interface AlfaPaymentResource extends ContractPaymentResource {
  contract: {
    company: Company
  }
  client: PersonResource
  purpose: string
}

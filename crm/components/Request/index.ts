import type { PassResource } from '~/components/Pass'

export interface RequestListResource {
  id: number
  status: RequestStatus
  direction: Direction | null
  responsible_user: PersonResource | null
  client: PersonResource | null
  phones: PhoneResource[]
  created_at: string
  comments_count: number
  passes: PassResource[]
  user_id: number | null
  is_verified: boolean
  associated_clients: PersonResource[]
  associated_requests_count: number
}

export interface RequestResource {
  id?: number
  status: RequestStatus
  direction: Direction | null
  responsible_user_id: number | null
  yandex_id: string | null
  google_id: string | null
  ip: string | null
  phones: PhoneResource[]
  user?: PersonResource
  client_id: number | null
  source?: string
  created_at?: string
  associated_clients: ClientWithContractsResource[]
  is_verified?: boolean
}

export const modelDefaults: RequestResource = {
  status: 'new',
  direction: null,
  responsible_user_id: null,
  phones: [],
  associated_clients: [],
  client_id: null,
  yandex_id: null,
  google_id: null,
  ip: null,
  is_verified: true,
}

export const apiUrl = 'requests'

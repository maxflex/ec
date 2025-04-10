export interface PassResource {
  id: number
  date: string
  name: string
  comment: string | null
  used_at: string | null
  is_expired: boolean
  request_id?: number
  user?: PersonResource
  created_at?: string
  request?: {
    id: number
    direction: Direction
  }
}

export const modelDefaults: PassResource = {
  id: newId(),
  date: '',
  name: '',
  comment: null,
  used_at: null,
  is_expired: false,
}

export const apiUrl = 'passes'

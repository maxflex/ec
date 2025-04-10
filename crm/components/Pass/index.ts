export interface PassResource {
  id: number
  date: string
  comment: string
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
  comment: '',
  used_at: null,
  is_expired: false,
}

export const apiUrl = 'passes'

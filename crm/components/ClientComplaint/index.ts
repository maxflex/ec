export interface ClientComplaintResource {
  id: number
  client_id: number
  teacher_id?: number
  program?: Program
  text: string
  user?: PersonResource
}

export interface ClientComplaintListResource {
  id: number
  client: PersonResource
  teacher: PersonResource
  program: Program
  text: string
  created_at: string
}

export const modelDefaults: ClientComplaintResource = {
  id: newId(),
  client_id: -1,
  text: '',
}

export const apiUrl = 'client-complaints'

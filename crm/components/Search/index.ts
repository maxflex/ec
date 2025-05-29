import type { RequestListResource } from '../Request'

export interface SearchResultResource extends PersonWithPhotoResource, HasPhones {
  entity_type: EntityType
  is_active: boolean
  client?: {
    directions: ClientDirections
    max_contract_year: Year | null
  }
  teacher?: {
    status: TeacherStatus
    subjects: Subject[]
  }
  contract?: {
    year: Year
    company: Company
    programs_count: number
    directions: Direction[]
    client_id: number
  }
  request?: RequestListResource
}

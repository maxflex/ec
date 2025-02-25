import type { RequestListResource } from '../Request'

export interface SearchResultResource extends PersonWithPhotoResource, HasPhones {
  entity_type: EntityType
  is_active: boolean
  client?: {
    directions: Direction[]
    max_contract_year: Year | null
  }
  teacher?: {
    status: TeacherStatus
    subjects: Subject[]
  }
  request?: RequestListResource
}

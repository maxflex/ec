import type { RequestListResource } from '../Request'

export interface SearchResultResource extends PersonWithPhotoResource, HasPhones {
  entity_type: EntityType
  is_active: boolean
  client?: {
    directions: Direction[]
    contract_versions: ContractVersionListResource[]
  }
  teacher?: {
    status: TeacherStatus
    subjects: Subject[]
  }
  request?: RequestListResource
}

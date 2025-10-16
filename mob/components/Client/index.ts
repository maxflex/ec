import type { ContractVersionResource } from '../ContractVersion'
import type { CurrentLessonResource } from '../Lesson'

export interface RepresentativeResource extends HasName, HasPhones {
  id: number
  email?: string
  passport: {
    series: string | null
    number: string | null
    address: string | null
    code: string | null
    issued_date: string | null
    issued_by: string | null
    fact_address: string | null
  }
}

export interface ClientListResource extends PersonResource {
  schedule?: Teeth
  directions: ClientDirections
  created_at: string
}

export interface ClientWithContractsResource extends PersonResource {
  contract_versions: ContractVersionResource[]
}

export type MarkSheet = Partial<Record<Subject, number>>

export interface ClientResource extends PersonWithPhotoResource, HasPhones {
  bio: string | null
  branches: Branch[]
  directions: ClientDirections
  head_teacher_id: number | null
  head_teacher?: PersonResource
  representative: RepresentativeResource
  is_remote: boolean
  user?: PersonResource
  is_risk: boolean
  is_consult_agree: boolean
  created_at?: string
  email?: string
  heard_about_us?: HeardAboutUs
  mark_sheet: MarkSheet | null
  schedule: Teeth | null
  current_lesson: null | CurrentLessonResource
  passport: {
    series: string | null
    number: string | null
    birthdate: string | null
  }
}

export const modelDefaults: ClientResource = {
  id: newId(),
  first_name: null,
  last_name: null,
  middle_name: null,
  head_teacher_id: null,
  branches: [],
  directions: {},
  phones: [],
  photo_url: null,
  is_remote: false,
  is_risk: false,
  is_consult_agree: false,
  entity_type: EntityTypeValue.client,
  mark_sheet: null,
  passport: {
    series: null,
    number: null,
    birthdate: null,
  },
  representative: {
    id: newId(),
    first_name: null,
    last_name: null,
    middle_name: null,
    phones: [],
    passport: {
      series: null,
      number: null,
      address: null,
      code: null,
      issued_date: null,
      issued_by: null,
      fact_address: null,
    },
  },
  bio: null,
  schedule: null,
  current_lesson: null,
}

export const apiUrl = 'clients'

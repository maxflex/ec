import type { ContractVersionResource } from '../ContractVersion'

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
  created_at?: string
  email?: string
  heard_about_us?: HeardAboutUs
  mark_sheet: MarkSheet | null
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
}

export const apiUrl = 'clients'

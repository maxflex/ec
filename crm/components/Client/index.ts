export interface ParentResource extends HasName, HasPhones {
  id: number
  email?: string
  last_seen_at?: string
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
  directions: Direction[]
  created_at: string
}

export interface ClientWithContractsResource extends PersonResource {
  contract_versions: ContractVersionResource[]
}

export type MarkSheet = Partial<Record<Subject, number>>

export interface ClientResource extends PersonWithPhotoResource, HasPhones {
  branches: Branch[]
  directions: Direction[]
  head_teacher_id: number | null
  head_teacher?: PersonResource
  parent: ParentResource
  is_remote: boolean
  can_login?: boolean
  user?: PersonResource
  created_at?: string
  email?: string
  heard_about_us?: HeardAboutUs
  last_seen_at?: string
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
  directions: [],
  phones: [],
  photo_url: null,
  is_remote: false,
  entity_type: EntityTypeValue.client,
  mark_sheet: null,
  passport: {
    series: null,
    number: null,
    birthdate: null,
  },
  parent: {
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

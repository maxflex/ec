export const ErrorCodeLabel = [1000, 2000] as const

export type ErrorCode = (typeof ErrorCodeLabel)[number]

export interface ErrorResource {
  id: number
  code: ErrorCode
  entity_id: number
  entity_type: EntityType
  number?: string
  person: PersonResource
}

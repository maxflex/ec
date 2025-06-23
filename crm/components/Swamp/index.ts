export interface SwampListResource {
  id: number
  total_lessons: number
  lessons_conducted: number
  client: PersonResource
  program: Program
  year: Year
  contract_id: number
  status: SwampStatus
  group: GroupListResource | null
}

export interface SwampCountsResource {
  client: PersonResource
  counts: Record<SwampStatus, number>
}

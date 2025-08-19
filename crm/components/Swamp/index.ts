export interface SwampListResource {
  id: number
  total_lessons: number
  lessons_conducted: number
  client: PersonResource
  program: Program
  year: Year
  contract_id: number
  status: CvpStatus
  client_group_id: number | null
  group: GroupListResource | null
}

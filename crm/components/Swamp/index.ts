import type { GroupListResource } from '../Group'

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
  changes: null | {
    schedule_draft_id: number
    type: 'changed' | 'added' | 'removed'
    group: GroupListResource | null
  }
}

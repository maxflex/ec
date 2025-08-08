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

export const SwampStatusLabelExtended = {
  active_no_group: 'к исполнению',
  active_in_group: 'к исполнению <br/>в группе',
  finished_no_group: 'исполнено',
  finished_in_group: 'исполнено <br />в группе',
  exceeded_no_group: 'перевыполнено',
  exceeded_in_group: 'перевыполнено <br />в группе',
} as const

export interface SwampCountsResource {
  client: PersonResource
  counts: Record<keyof typeof SwampStatusLabelExtended, number>
}

// export function getSwampStatus(status: CvpStatus, groupId: number | null | undefined) {
//   switch (status) {
//     case 'active':
//       return groupId ? 'исполняется' : 'к исполнению'

//     case 'finished':
//       return groupId ? 'исполнено + в группе' : 'исполнено + не в группе'

//     case 'exceeded':
//       return groupId ? 'перевыполнено + в группе' : 'перевыполнено + не в группе'
//   }
// }

export interface AddToGroupItem {
  id: number // cvp id
  client: PersonResource
  uncunducted_count: number
  overlap: ScheduleOverlap
  contract_id: number
  teeth: Teeth
  group_id: number | null
  has_problems: boolean
  current_contract_id: number | null
  lessons_conducted: number
  total_lessons: number
  status: CvpStatus
}

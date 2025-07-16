export const apiUrl = 'schedule-drafts'

/**
 * Сохраненные проекты расписания
 */
export interface ScheduleDraftResource {
  id: number
  user: PersonResource
  created_at: string
}

/**
 * Процесс по договору
 */
interface ScheduleDraftSwamp {
  id: number // cvp_id
  status: SwampStatus
  total_lessons: number
  lessons_conducted: number
  client_group_id: number
}

export interface ScheduleDraftGroup extends GroupListResource {
  /**
   * Пересечения в расписании
   */
  overlap?: {
    count: number
    programs: Program[]
  }
  swamp?: ScheduleDraftSwamp
  uncunducted_count: number
  draft_status?: number
}

export interface ScheduleDraftProgram {
  id: number // ID contract_version_programs
  program: Program
  group_id?: number
  contract_id?: number
  swamp?: ScheduleDraftSwamp
  groups: ScheduleDraftGroup[]
}

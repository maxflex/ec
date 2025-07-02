export const apiUrl = 'schedule-drafts'

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
}

export interface ScheduleDraftProgram {
  id: number // ID contract_version_programs
  program: Program
  group_id?: number
  contract?: ContractResource
  swamp?: ScheduleDraftSwamp
  groups: ScheduleDraftGroup[]
}

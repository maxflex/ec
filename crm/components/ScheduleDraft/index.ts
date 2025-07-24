export const apiUrl = 'schedule-drafts'

/**
 * Сохраненные проекты расписания
 */
export interface SavedScheduleDraftResource {
  id: number
  contract_id: number | null
  user: PersonResource
  client: PersonResource
  programs: any[]
  year: Year
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
  contract_id: number | null
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
  /**
   * По какой программе был добавлен в группу изначально
   */
  original_contract_id: number | null

  /**
   * По какой программе добавлен в группу сейчас
   */
  current_contract_id: number | null
}

export interface ScheduleDraftProgram {
  id: number // ID contract_version_programs
  program: Program
  group_id?: number
  contract_id?: number
  swamp?: ScheduleDraftSwamp
  groups: ScheduleDraftGroup[]
}

export type ScheduleDraft = Record<number, ScheduleDraftProgram[]>

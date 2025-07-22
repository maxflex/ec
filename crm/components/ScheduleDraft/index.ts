export const apiUrl = 'schedule-drafts'

export type DraftStatus = 'added' | 'removed'

/**
 * Сохраненные проекты расписания
 */
export interface SavedScheduleDraft {
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
  draft_status: DraftStatus | null
}

export interface ScheduleDraftProgram {
  id: number // ID contract_version_programs
  program: Program
  group_id?: number
  contract_id?: number
  swamp?: ScheduleDraftSwamp
  groups: ScheduleDraftGroup[]
}

export interface ScheduleDraftContract {
  contract_id: number | null
  is_active: boolean
  programs: ScheduleDraftProgram[]
}

export type ScheduleDraft = ScheduleDraftContract[]

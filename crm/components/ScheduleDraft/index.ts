export const apiUrl = 'schedule-drafts'

/**
 * Сохраненные проекты расписания
 */
export interface SavedScheduleDraftResource {
  id: number
  user: PersonResource
  client: PersonResource
  year: Year
  created_at: string
  is_archived: boolean
  changes: number
  contract_id: number | null
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

export function isGroupChangedInContract(group: ScheduleDraftGroup, contractId: number): boolean {
  const from = group.original_contract_id
  const to = group.current_contract_id

  const wasInThisContract = from === contractId
  const isInThisContractNow = to === contractId
  const moved = from !== to

  // Ушло из этого договора
  if (wasInThisContract && !isInThisContractNow && moved)
    return true

  // Пришло в этот договор (в том числе из другого)
  if (isInThisContractNow && !wasInThisContract && moved)
    return true

  return false
}

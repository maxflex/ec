import type { GroupListResource } from '../Group'

export const apiUrl = 'projects'

/**
 * Сохраненные проекты расписания
 */
export interface SavedProjectResource {
  id: number
  user: PersonResource
  client: PersonResource
  year: Year
  created_at: string
  is_archived: boolean
  changes: number
  contract_id: number | null
  has_problems_in_list: boolean
}

/**
 * Процесс по договору
 */
interface ProjectSwamp {
  id: number // cvp_id
  status: CvpStatus
  total_lessons: number
  lessons_conducted: number
  client_group_id: number
  contract_id: number | null
}

export interface ProjectGroup extends GroupListResource {
  /**
   * Пересечения в расписании
   */
  overlap: ScheduleOverlap
  swamp?: ProjectSwamp
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

export interface ProjectProgram {
  id: number // ID contract_version_programs
  program: Program
  group_id?: number
  contract_id?: number
  swamp?: ProjectSwamp
  groups: ProjectGroup[]
}

export type Project = Record<number, ProjectProgram[]>

export function isGroupChangedInContract(group: ProjectGroup, contractId: number): boolean {
  const from = group.original_contract_id
  const to = group.current_contract_id

  const wasInThisContract = from === contractId
  const isInThisContractNow = to === contractId
  const moved = from !== to

  // Ушло из этого договора
  if (wasInThisContract && !isInThisContractNow && moved) {
    return true
  }

  // Пришло в этот договор (в том числе из другого)
  if (isInThisContractNow && !wasInThisContract && moved) {
    return true
  }

  return false
}

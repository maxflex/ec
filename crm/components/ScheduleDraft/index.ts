export const apiUrl = 'schedule-drafts'

export interface SwampEditorProgram {
  id: number
  program: Program
}

export interface SwampEditorGroup extends GroupListResource {

  /**
   * В режиме "управление группами" показывает процесс по договору, если он есть
   */
  swamp?: {
    id: number
    status: SwampStatus
    total_lessons: number
    lessons_conducted: number
    client_group_id: number
  }
  /**
   * В режиме "управление группами": кол-во пересечений в расписании
   */
  overlap?: {
    count: number
    programs: Program[]
  }
}

export type SwampEditorData = Record<number, {
  contract?: ContractResource
  swamp?: {
    id: number // ID client_groups
    status: SwampStatus
    total_lessons: number
    lessons_conducted: number
    group_id: number
  }
  groups: SwampEditorGroup[]
}>

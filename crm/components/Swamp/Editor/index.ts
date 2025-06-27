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
  /**
   * В режиме "управление группами": использована ли программа?
   */
  is_program_used?: boolean
}

export type SwampEditorData = Record<number, {
  contract: ContractResource
  swamp: {
    id: number
    status: SwampStatus
    total_lessons: number
    lessons_conducted: number
  }
  groups: SwampEditorGroup[]
}>

// при каких программах можно редактировать четверть
export const quarterEditablePrograms: Program[] = [
  'geoSchool8',
  'bioSchool8',
  'socSchool8',
  'chemSchool8',
  'infSchool8',
  'physSchool8',
  'mathSchool8',
  'engSchool8',
  'litSchool8',
  'rusSchool8',
  'hisSchool8',
  'bioSchool9',
  'mathSchool9',
  'rusSchool9',
  'infSchool9',
  'physSchool9',
  'chemSchool9',
  'engSchool9',
  'socSchool9',
  'hisSchool9',
  'litSchool9',
  'geoSchool9',
]

export interface CurrentLessonResource {
  id: number
  time: string
  time_end: string
  cabinet: string
  teacher: PersonResource
  group: {
    id: number
    program: Program
  }
}

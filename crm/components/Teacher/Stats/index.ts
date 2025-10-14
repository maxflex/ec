export const apiUrl = `teacher-stats-new`

export interface TeacherStatsItem {
  // ОПОЗДАНИЯ ПРОВОДКИ

  /** Количество проведенных занятий */
  lessons_conducted: number

  /** Количество занятий, проведенных с опозданием (не в дату занятия) */
  lessons_conducted_next_day: number

  // ПОСЕЩАЕМОСТЬ

  /** Суммарное количество детей, посетивших занятия в текущую дату (во всех статусах) */
  client_lessons: number

  /** Среднее количество учеников в проведенных занятиях */
  client_lessons_avg: number

  /** Количество пропусков, то есть "не был" */
  client_lessons_absent: number

  /** Количество опозданий, то есть "опоздал"+"опоздал дист." */
  client_lessons_late: number

  /** Количество удаленки, то есть "дист"+"опоздал дист." */
  client_lessons_online: number

  /** Доля пропусков: "не был" / суммарное количество посещений во всех статусах */
  client_lessons_absent_share: number

  /** Доля опозданий: ("опоздал"+"опоздал дист.") / суммарное количество посещений во всех статусах */
  client_lessons_late_share: number

  /** Доля удаленки: ("дист"+"опоздал дист.") / суммарное количество посещений во всех статусах */
  client_lessons_online_share: number

  // УДЕРЖАНИЕ АУДИТОРИИ

  /** Количество детей, начавших заниматься у преподавателя (client_ID*teacher_ID*program*year) */
  retention_new: number

  /** Количество детей, прекративших заниматься (по дате последнего занятия в client_lessons) в конфигурации client_ID*teacher_ID*program*year */
  retention_left: number

  /** Удержание % */
  retention_share: number

  /** Ушло (100% - удержание%) */
  // retention_left_share: number

  // ВЕДОМОСТЬ

  /** Количество занятий, в которых дом.задание НЕ = NULL */
  lessons_with_homework: number
  lessons_with_homework_avg: number

  /** Количество занятий, в которых есть хотя бы 1 прикрепленный файл */
  lessons_with_files: number
  lessons_with_files_avg: number

  /** Количество выставленных оценок */
  scores: number

  /** Средняя оценка по занятиям */
  scores_avg: number

  scores_share: number

  /** Количество оставленных комментариев к оценкам */
  scores_comments: number

  /** Количество оставленных общих комментариев */
  comments: number

  // ОТЧЕТЫ

  /** Количество отчетов в статусе опубликовано */
  reports_published: number

  /** Количество отчетов в статусе опубликовано без начисления */
  reports_published_no_price: number

  /** Средняя заполняемость отчетов */
  reports_fill_avg: number

  /** Средняя оценка по отчетам */
  reports_grade_avg: number
}

export type TeacherStatsByDate = Record<string, TeacherStatsItem>

export const TeacherStatsModeLabel = {
  day: 'по дням',
  week: 'по неделям',
  month: 'по месяцам',
} as const

export type TeacherStatsMode = keyof typeof TeacherStatsModeLabel

export interface TeacherStats {
  items: TeacherStatsByDate
  totals: TeacherStatsItem
}

export type TeacherStatsField = keyof TeacherStatsItem

export const labels: Partial<Record<TeacherStatsField, string>> = {
  // ОПОЗДАНИЯ ПРОВОДКИ
  lessons_conducted: 'провед.',
  lessons_conducted_next_day: 'c опозд.',

  // ПОСЕЩАЕМОСТЬ
  client_lessons: 'посещ.',
  client_lessons_avg: 'сред.уч.',
  client_lessons_absent: 'проп.',
  client_lessons_late: 'опозд.',
  client_lessons_online: 'дист.',
  // client_lessons_absent_share: '%проп.',
  // client_lessons_late_share: '%опозд.',
  // client_lessons_online_share: '%удал.',

  // УДЕРЖАНИЕ АУДИТОРИИ
  retention_new: 'новых',
  retention_left: 'ушло',
  // retention_share: '%удерж.',

  // ВЕДОМОСТЬ
  lessons_with_homework: 'дз',
  lessons_with_files: 'файлы',
  scores: 'оценки',
  scores_avg: 'сред.оц.',
  scores_comments: 'комм.оц.',
  comments: 'комм.',

  // ОТЧЁТЫ
  reports_published: 'отчеты',
  reports_published_no_price: 'без нач.',
  reports_fill_avg: '%заполн.',
  reports_grade_avg: 'сред.оц.',

  // // ОПОЗДАНИЯ ПРОВОДКИ
  // lessons_conducted: 'проведено',
  // lessons_conducted_next_day: 'с опозданием',

  // // ПОСЕЩАЕМОСТЬ
  // client_lessons: 'посещений',
  // client_lessons_avg: 'посещений сред.',
  // client_lessons_absent: 'пропуски',
  // client_lessons_late: 'опоздания',
  // client_lessons_online: 'удалёнка',
  // client_lessons_absent_share: '% пропусков',
  // client_lessons_late_share: '% опозданий',
  // client_lessons_online_share: '% удалёнки',

  // // УДЕРЖАНИЕ АУДИТОРИИ
  // retention_new: 'новых',
  // retention_left: 'ушедших',
  // retention_share: '% удержания',

  // // ВЕДОМОСТЬ
  // lessons_with_homework: 'с дз',
  // lessons_with_files: 'с файлами',
  // scores: 'оценок',
  // scores_avg: 'средн. оценка',
  // scores_comments: 'комм. к оценкам',
  // comments: 'комм. общий',

  // // ОТЧЁТЫ
  // reports_published: 'отчётов',
  // reports_published_no_price: 'без начисл.',
  // reports_fill_avg: 'заполн. %',
  // reports_grade_avg: 'оценка отчёта',
}

export const tooltips: Partial<Record<TeacherStatsField, string>> = {
  // ОПОЗДАНИЯ ПРОВОДКИ
  lessons_conducted: 'Общее количество проведённых занятий',
  lessons_conducted_next_day: 'Количество занятий, которые были проведены с опозданием (не в день занятия)',

  // ПОСЕЩАЕМОСТЬ
  client_lessons: 'Суммарное количество посещений (во всех статусах)',
  client_lessons_avg: 'Среднее количество учеников на одном проведённом занятии',
  client_lessons_absent: 'Пропуски – количество посещений в статусе «не был». Рядом указан % от общего количества посещений',
  client_lessons_late: 'Опоздания – количество посещений в статусах «опоздал» + «опоздал дист.». Рядом указан % от общего количества посещений',
  client_lessons_online: 'Дистанционно – количество посещений в статусах «дист.» + «опоздал дист.». Рядом указан % от общего количества посещений',
  // client_lessons_absent_share: 'Доля пропусков = пропуски / общее число посещений × 100%.',
  // client_lessons_late_share: 'Доля опозданий = опоздания / общее число посещений × 100%.',
  // client_lessons_online_share: 'Доля онлайн = онлайн / общее число посещений × 100%.',

  // УДЕРЖАНИЕ АУДИТОРИИ
  retention_new: 'Количество учеников, которые впервые пришли к преподавателю по этой программе',
  retention_left: 'Количество учеников, у которых занятие по этой программе было последним',
  // retention_share: 'Доля удержания = активные ученики / общее количество учеников × 100%.',

  // ВЕДОМОСТЬ
  lessons_with_homework: 'Количество занятий с заполненным домашним заданием',
  lessons_with_files: 'Количество занятий, где есть хотя бы один прикреплённый файл',
  scores: 'Количество выставленных оценок',
  scores_avg: 'Средняя оценка среди всех выставленных оценок',
  scores_comments: 'Количество комментариев к выставленным оценкам',
  comments: 'Количество общих комментариев к посещеням',

  // ОТЧЁТЫ
  reports_published: 'Количество отчётов в статусе «опубликовано»',
  reports_published_no_price: 'Количество опубликованных отчётов без начисления',
  reports_fill_avg: 'Средний процент заполненности отчётов',
  reports_grade_avg: 'Средняя оценка по опубликованным отчётам',
}

export const grayFields: Partial<Record<TeacherStatsField, TeacherStatsField>> = {
  client_lessons_absent: 'client_lessons_absent_share',
  client_lessons_late: 'client_lessons_late_share',
  client_lessons_online: 'client_lessons_online_share',
  retention_left: 'retention_share',
  lessons_with_files: 'lessons_with_files_avg',
  lessons_with_homework: 'lessons_with_homework_avg',
  scores: 'scores_share',
}

export const percentFields: TeacherStatsField[] = [
  'client_lessons_late_share',
  'client_lessons_absent_share',
  'client_lessons_online_share',
  'lessons_with_homework_avg',
  'lessons_with_files_avg',
  'retention_share',
  'reports_fill_avg',
  'scores_share',
]

export const avgFields: TeacherStatsField[] = [
  'client_lessons_avg',
  'scores_avg',
]

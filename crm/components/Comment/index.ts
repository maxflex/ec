export const commentTabs = {
  '': 'общие',
  'control-lk': 'контроль лк',
  'control-lessons': 'контроль занятий',
} as const

export type CommentTab = keyof typeof commentTabs

import {
  mdiAccount,
  mdiAccountGroup,
  mdiBookOpenBlankVariantOutline,
  mdiCalendar,
  mdiCreditCardCheckOutline,
  mdiFileDocumentEditOutline,
  mdiNumeric5BoxMultiple,
} from '@mdi/js'

export const clientMenu: MenuItem[] = [
  {
    icon: mdiBookOpenBlankVariantOutline,
    title: 'Дневник',
    to: '/journal',
  },
  {
    icon: mdiCalendar,
    title: 'Расписание',
    to: '/schedule',
  },
  {
    icon: mdiNumeric5BoxMultiple,
    title: 'Оценки',
    to: '/grades',
  },
  {
    icon: mdiFileDocumentEditOutline,
    title: 'Отчёты',
    to: '/reports',
  },
  {
    icon: mdiCreditCardCheckOutline,
    title: 'Оплата обучения',
    to: '/billing',
  },
  {
    icon: mdiAccount,
    title: 'Мой профиль',
    to: '/profile',
  },
]

export const adminMenu: MenuItem[] = [
  {
    icon: mdiAccountGroup,
    title: 'Договоры',
    to: '/contracts',
  },
  {
    icon: mdiAccount,
    title: 'Мой профиль',
    to: '/profile',
  },
]

import {
  mdiAccount,
  mdiAccountGroup,
  mdiBookOpenBlankVariantOutline,
  mdiCalendar,
  mdiCreditCardCheckOutline,
  mdiDotsTriangle,
  mdiFileDocumentEditOutline,
  mdiNumeric5BoxMultiple,
  mdiTicket,
} from '@mdi/js'

export const clientMenu: MenuItem[] = [
  {
    icon: mdiBookOpenBlankVariantOutline,
    title: 'Дневник',
    to: '/journal',
  },
  // {
  //   icon: mdiCalendar,
  //   title: 'Расписание',
  //   to: '/schedule',
  // },
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
    icon: mdiDotsTriangle,
    title: 'Тесты',
    to: '/tests',
  },
  {
    icon: mdiTicket,
    title: 'События',
    to: '/events',
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

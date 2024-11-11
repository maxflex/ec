<script setup lang="ts">
import {
  mdiAccount,
  mdiAccountGroup,
  mdiAccountMultiple,
  mdiAccountMultipleCheck,
  mdiAccountMultipleOutline,
  mdiAlertCircleOutline,
  mdiBookOpenOutline,
  mdiCalendar,
  mdiCalendarBadge,
  mdiCalendarEdit,
  mdiCalendarStar,
  mdiCash,
  mdiDotsTriangle,
  mdiFileDocumentEditOutline,
  mdiFileDocumentOutline,
  mdiFileSign,
  mdiFinance,
  mdiHistory,
  mdiHumanMaleBoard,
  mdiInbox,
  mdiInvoiceTextSend,
  mdiLogout,
  mdiNumeric5BoxMultiple,
  mdiPrinter,
  mdiSendCircle,
  mdiStarBox,
  mdiStarBoxOutline,
} from '@mdi/js'
import { missedCount, openCallApp } from '~/components/CallApp'

const { logOut } = useAuthStore()

const menu: Menu = [
  {
    icon: mdiInbox,
    title: 'Заявки',
    to: '/requests',
  },
  {
    icon: mdiFileDocumentOutline,
    title: 'Договоры',
    to: '/contracts',
  },
  {
    icon: mdiAccountGroup,
    title: 'Группы',
    to: '/groups',
  },
  {
    icon: mdiAccountMultipleOutline,
    title: 'Болота',
    to: '/swamps',
  },
  {
    icon: mdiAccount,
    title: 'Клиенты',
    to: '/clients',
  },
  {
    icon: mdiHumanMaleBoard,
    title: 'Преподаватели',
    to: '/teachers',
  },
  {
    icon: mdiBookOpenOutline,
    title: 'Темы',
    to: '/topics',
  },
  {
    icon: '$pass',
    title: 'Пропуски',
    to: '/passes',
  },
  {
    icon: '$pass',
    title: 'Постоянные пропуски',
    to: '/passes/permanent',
  },
  {
    icon: mdiFileDocumentEditOutline,
    title: 'Отчёты',
    to: '/reports',
  },
  {
    icon: mdiFileDocumentEditOutline,
    title: 'Отчёты КР',
    to: '/head-teacher-reports',
  },
  {
    icon: mdiStarBoxOutline,
    title: 'Отзывы',
    to: '/client-reviews',
  },
  {
    icon: mdiStarBox,
    title: 'Отзывы на сайте',
    to: '/web-reviews',
  },
  {
    icon: mdiCash,
    title: 'Платежи клиентов',
    to: '/client-payments',
  },
  {
    icon: mdiCash,
    title: 'Платежи препод',
    to: '/teacher-payments',
  },
  {
    icon: mdiCash,
    title: 'Балансы препод',
    to: '/teacher-balances',
  },
  {
    icon: mdiCash,
    title: 'Балансы договоров',
    to: '/contract-balances',
  },
  {
    icon: mdiCash,
    title: 'Допуслуги',
    to: '/teacher-services',
  },
  {
    icon: mdiFinance,
    title: 'Итоги',
    to: '/stats',
  },
  {
    icon: mdiNumeric5BoxMultiple,
    title: 'Оценки',
    to: '/grades',
  },
  {
    icon: mdiAccountMultipleCheck,
    title: 'Выбрать людей',
    to: '/people-selector',
  },
  {
    icon: mdiInvoiceTextSend,
    title: 'Проекты отправок',
    to: '/telegram-lists',
  },
  {
    icon: mdiSendCircle,
    title: 'Сообщения',
    to: '/telegram-messages',
  },
  {
    icon: mdiFileSign,
    title: 'Инструкции',
    to: '/instructions',
  },
  {
    icon: mdiCalendar,
    title: 'Занятия',
    to: '/lessons',
    count: 'schedule',
  },
  {
    icon: mdiCalendarBadge,
    title: 'События',
    to: '/events',
  },
  {
    icon: mdiCalendarEdit,
    title: 'Экзамены',
    to: '/exam-dates',
  },
  {
    icon: mdiCalendarStar,
    title: 'Праздники',
    to: '/vacations',
  },
  {
    icon: mdiPrinter,
    title: 'Макросы',
    to: '/macros',
  },
  {
    icon: mdiDotsTriangle,
    title: 'Тесты',
    to: '/tests',
  },
  {
    icon: mdiDotsTriangle,
    title: 'Тесты клиентов',
    to: '/client-tests',
  },
  {
    icon: mdiAccountMultiple,
    title: 'Пользователи',
    to: '/users',
  },
  {
    icon: mdiHistory,
    title: 'Логи',
    to: '/logs',
  },
  {
    icon: mdiAlertCircleOutline,
    title: 'Ошибки',
    to: '/errors',
  },
]

nextTick(updateMenuCounts)
</script>

<template>
  <v-list nav density="compact">
    <v-list-item :to="{ name: 'search' }">
      <template #prepend>
        <v-icon icon="$search" />
      </template>
      Поиск
    </v-list-item>
    <v-list-item :active="false" @click="openCallApp()">
      <template #prepend>
        <CallAppStateIcon />
      </template>
      Звонки
      <template #append>
        <v-fade-transition>
          <v-badge
            v-if="missedCount"
            color="error"
            inline
            :content="missedCount"
          />
        </v-fade-transition>
      </template>
    </v-list-item>

    <MenuList :items="menu" />

    <v-list-item @click="logOut()">
      <template #prepend>
        <v-icon :icon="mdiLogout" />
      </template>
      Выход
    </v-list-item>
  </v-list>
  <CallAppMain />
</template>

<script setup lang="ts">
import {
  mdiAccount,
  mdiAccountGroup,
  mdiCalendar,
  mdiCashMultiple,
  mdiFileDocumentEditOutline,
  mdiHumanMaleBoard,
  mdiInbox,
  mdiLockOpenOutline,
  mdiLogout,
  mdiSeatPassenger,
  mdiSendCircle,
} from '@mdi/js'
import { callAppDialog } from '~/components/CallApp'

const { $addSseListener, $removeSseListener } = useNuxtApp()

const { logOut } = useAuthStore()

const menu: Menu = [
  {
    icon: mdiInbox,
    title: 'Заявки',
    to: '/requests',
    count: true,
  },
  {
    icon: mdiAccount,
    title: 'Клиенты',
    items: [
      { title: 'Договоры', to: '/contracts' },
      { title: 'Проекты', to: '/projects' },
      { title: 'Жалобы', to: '/client-complaints' },
      { title: 'Отзывы', to: '/client-reviews' },
      { title: 'Контроль ЛК', to: '/control/lk' },
      { title: 'Контроль занятий', to: '/control/lessons' },
      { title: 'Контроль оценок', to: '/control/grades' },
      { title: 'Пройденные тесты', to: '/client-tests' },
    ],
  },
  {
    icon: mdiAccountGroup,
    title: 'Группы',
    items: [
      { title: 'Все группы', to: '/groups' },
      { title: 'Болота', to: '/swamps' },
    ],
  },
  {
    icon: mdiCalendar,
    title: 'Занятия',
    items: [
      { title: 'Все занятия', to: '/all-lessons' },
      { title: 'Темы', to: '/topics' },
      { title: 'Итоговые оценки', to: '/grades' },
    ],
  },
  {
    icon: mdiHumanMaleBoard,
    title: 'Преподаватели',
    items: [
      { title: 'Все преподаватели', to: '/teachers' },
      { title: 'Инструкции', to: '/instructions' },
    ],
  },
  {
    icon: mdiFileDocumentEditOutline,
    title: 'Отчёты',
    items: [
      { title: 'Все отчёты', to: '/reports' },
      { title: 'Отчёты КР', to: '/head-teacher-reports' },
    ],
  },
  {
    icon: mdiCashMultiple,
    title: 'Финансы',
    items: [
      { title: 'Платежи клиентов', to: '/all-payments' },
      { title: 'Платежи препод', to: '/teacher-payments' },
      { title: 'Балансы препод', to: '/teacher-balances' },
      { title: 'Балансы договоров', to: '/contract-balances' },
      { title: 'Допуслуги', to: '/teacher-services' },
    ],
  },
  {
    icon: mdiSendCircle,
    title: 'Сообщения',
    items: [
      { title: 'История Telegram', to: '/telegram-messages' },
      { title: 'История SMS', to: '/sms-messages' },
      { title: 'Групповое сообщение', to: '/people-selector' },
      { title: 'Рассылки', to: '/telegram-lists' },
    ],
  },
  {
    icon: '$pass',
    title: 'Пропуски',
    items: [
      { title: 'Разовые', to: '/passes' },
      { title: 'Постоянные', to: '/passes/permanent' },
      { title: 'Статистика', to: '/passes/stats' },
    ],
  },
  {
    icon: mdiSeatPassenger,
    title: 'Кабинеты',
    to: '/cabinets',
  },
  {
    icon: mdiLockOpenOutline,
    title: 'Административное',
    items: [
      { title: 'Итоги', to: '/stats' },
      { title: 'Пользователи', to: '/users' },
      { title: 'Отзывы', to: '/web-reviews' },
      { title: 'Праздники', to: '/vacations' },
      { title: 'Экзамены', to: '/exams' },
      { title: 'События', to: '/events' },
      { title: 'Тесты', to: '/tests' },
      { title: 'Ошибки', to: '/errors' },
      { title: 'Логи', to: '/logs' },
      { title: 'Макросы', to: '/macros' },
    ],
  },
]

$addSseListener('RequestUpdatedEvent', updateMenuCounts)
onUnmounted(() => $removeSseListener('RequestUpdatedEvent'))
nextTick(updateMenuCounts)
</script>

<template>
  <v-list nav density="compact" open-strategy="single">
    <v-list-item :to="{ name: 'search' }">
      <template #prepend>
        <v-icon icon="$search" />
      </template>
      Поиск
    </v-list-item>
    <v-list-item :active="false" @click="callAppDialog = true">
      <template #prepend>
        <CallAppStateIcon />
      </template>
      Звонки
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

<script setup lang="ts">
import type { Menu, MenuCountsUpdatedPayload } from '.'
import {
  mdiAccount,
  mdiAccountGroup,
  mdiCalendar,
  mdiCardAccountDetailsOutline,
  mdiCashMultiple,
  mdiChatOutline,
  mdiFileDocumentEditOutline,
  mdiHumanMaleBoard,
  mdiInbox,
  mdiLockOpenOutline,
  mdiLogout,
  mdiPhone,
} from '@mdi/js'
import { getMenuCounts, onMenuCountsUpdated } from '.'

const { $addSseListener, $removeSseListener } = useNuxtApp()

const { logOut } = useAuthStore()

const menu: Menu = [
  {
    icon: mdiInbox,
    title: 'Заявки',
    to: '/requests',
    count: true,
  },
  // {
  //   icon: mdiPhone,
  //   title: 'Звонки',
  //   to: '/calls',
  //   count: true,
  // },
  {
    icon: mdiAccount,
    title: 'Клиенты',
    items: [
      { title: 'Активные клиенты', to: '/clients/active' },
      { title: 'Договоры', to: '/contracts' },
      { title: 'Проекты', to: '/projects' },
      { title: 'Жалобы', to: '/complaints' },
      { title: 'Отзывы', to: '/client-reviews' },
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
      { title: 'Нарушения', to: '/violations' },
      { title: 'Темы', to: '/topics' },
      { title: 'Кабинеты', to: '/cabinets' },
      { title: 'Итоговые оценки', to: '/grades' },
    ],
  },
  {
    icon: mdiHumanMaleBoard,
    title: 'Преподаватели',
    items: [
      { title: 'Все преподаватели', to: '/teachers' },
      { title: 'Инструкции', to: '/instructions' },
      { title: 'Жалобы', to: '/teacher-complaints' },
      { title: 'Договоры', to: '/teacher-contracts' },
      { title: 'Акты', to: '/teacher-acts' },
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
    icon: mdiChatOutline,
    title: 'Коммуникация',
    items: [
      {
        title: 'Звонки',
        to: '/calls',
        // count: true,
      },
      { title: 'Telegram', to: '/telegram-messages' },
      { title: 'SMS', to: '/sms-messages' },
      { title: 'Рассылки', to: '/telegram-lists' },
      { title: 'Групповое сообщение', to: '/group-message' },
    ],
  },
  {
    icon: mdiCardAccountDetailsOutline,
    title: 'Контроль',
    items: [
      { title: 'Контроль ЛК', to: '/control/lk' },
      { title: 'Контроль занятий', to: '/control/lessons' },
      { title: 'Контроль оценок', to: '/control/grades' },
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
    icon: mdiLockOpenOutline,
    title: 'Административное',
    items: [
      { title: 'Итоги', to: '/stats' },
      { title: 'ИИ', to: '/ai-prompts' },
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

$addSseListener('MenuCountsUpdatedEvent', onMenuCountsUpdated)
onUnmounted(() => $removeSseListener('MenuCountsUpdatedEvent'))
nextTick(getMenuCounts)
</script>

<template>
  <v-list nav density="compact" open-strategy="single">
    <v-list-item :to="{ name: 'search' }">
      <template #prepend>
        <v-icon icon="$search" />
      </template>
      Поиск
    </v-list-item>

    <MenuList :items="menu" />

    <v-list-item @click="logOut()">
      <template #prepend>
        <v-icon :icon="mdiLogout" />
      </template>
      Выход
    </v-list-item>
  </v-list>
</template>

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
    icon: mdiAccount,
    title: 'Клиенты',
    items: [
      { title: 'Все клиенты', to: '/clients' },
      { title: 'Договоры', to: '/contracts' },
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
      { title: 'Стипендия', to: '/scholarship-scores' },
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
    icon: mdiStarBoxOutline,
    title: 'Отзывы',
    items: [
      { title: 'Все отзывы', to: '/client-reviews' },
      { title: 'Отзывы на сайте', to: '/web-reviews' },
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
      { title: 'Отправленные', to: '/telegram-messages' },
      { title: 'Выбрать людей', to: '/people-selector' },
      { title: 'Проекты отправок', to: '/telegram-lists' },
    ],
  },
  {
    icon: mdiSeatPassenger,
    title: 'Кабинеты',
    items: [
      { title: 'Расписание', to: '/cabinets' },
      { title: 'Свободные кабинеты', to: '/cabinets/free' },
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
      { title: 'Пользователи', to: '/users' },
      { title: 'Праздники', to: '/vacations' },
      { title: 'Экзамены', to: '/exam-dates' },
      { title: 'События', to: '/events' },
      { title: 'Тесты', to: '/tests' },
      { title: 'Ошибки', to: '/errors' },
      { title: 'Логи', to: '/logs' },
      { title: 'Макросы', to: '/macros' },
    ],
  },
]

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

<script setup lang="ts">
import {
  mdiAccount,
  mdiAccountGroup,
  mdiAccountMultiple,
  mdiCalendar,
  mdiCash,
  mdiCurrencyRub,
  mdiDotsTriangle,
  mdiFileDocumentOutline,
  mdiFinance,
  mdiHistory,
  mdiHumanMaleBoard,
  mdiInbox,
  mdiLogout,
  mdiPrinter,
  mdiStarBox,
  mdiStarBoxOutline,
} from '@mdi/js'

const { user, logOut, clearCurrentToken } = useAuthStore()
let menu: Menu

switch (user?.entity_type) {
  case EntityType.client:
    menu = [
      {
        icon: mdiAccountGroup,
        title: 'Группы',
        to: '/groups',
      },
      {
        icon: mdiCalendar,
        title: 'Расписание',
        to: '/schedule',
      },
      {
        icon: mdiDotsTriangle,
        title: 'Тесты',
        to: '/tests',
      },
    ]
    break

  case EntityType.teacher:
    menu = [
      {
        icon: mdiAccountGroup,
        title: 'Группы',
        to: '/groups',
      },
      {
        icon: mdiCalendar,
        title: 'Расписание',
        to: '/schedule',
      },
      {
        icon: mdiCurrencyRub,
        title: 'Баланс',
        to: '/balance',
      },
    ]
    break

  default:
    menu = [
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
        icon: mdiStarBoxOutline,
        title: 'Отзывы',
        to: '/clients/reviews',
      },
      {
        icon: mdiStarBox,
        title: 'Отзывы на сайте',
        to: '/web-reviews',
      },
      {
        icon: mdiCash,
        title: 'Платежи клиентов',
        to: '/clients/payments',
      },
      {
        icon: mdiCash,
        title: 'Платежи препод',
        to: '/teachers/payments',
      },
      {
        icon: mdiFinance,
        title: 'Итоги',
        to: '/stats',
      },
      {
        icon: mdiCalendar,
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
        icon: mdiAccountMultiple,
        title: 'Пользователи',
        to: '/users',
      },
      {
        icon: mdiHistory,
        title: 'Логи',
        to: '/logs',
      },
    ]
}

async function exit() {
  await logOut()
  clearCurrentToken()
  const path = sessionStorage.getItem('redirect') || '/'
  window.location.href = path
}
</script>

<template>
  <div class="logo">
    <img src="/img/logo.svg">
    <h3>ЕГЭ-Центр</h3>
  </div>
  <v-list
    nav
    density="compact"
  >
    <v-list-item
      v-for="{ title, to, icon } in menu"
      :key="title"
      :to="to"
    >
      <template #prepend>
        <v-icon :icon="icon" />
      </template>
      {{ title }}
    </v-list-item>
    <v-list-item @click="exit()">
      <template #prepend>
        <v-icon :icon="mdiLogout" />
      </template>
      Выход
    </v-list-item>
  </v-list>
</template>

<style lang="sass">
.logo
  display: flex
  align-items: center
  justify-content: center
  padding: 20px 0
  img
    margin-right: 12px
  span
    font-size: 24px
    font-weight: 500
</style>

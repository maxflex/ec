<script setup lang="ts">
import {
  mdiAccount,
  mdiAccountGroup,
  mdiAccountMultiple,
  mdiBookOpenOutline,
  mdiCalendar,
  mdiCalendarBadge,
  mdiCalendarStar,
  mdiCash,
  mdiCreditCardCheckOutline,
  mdiCurrencyRub,
  mdiDotsTriangle,
  mdiFileDocumentEditOutline,
  mdiFileDocumentOutline,
  mdiFileSign,
  mdiFinance,
  mdiHistory,
  mdiHumanMaleBoard,
  mdiInbox,
  mdiLogout,
  mdiNumeric5BoxMultiple,
  mdiPrinter,
  mdiSendCircle,
  mdiStarBox,
  mdiStarBoxOutline,
} from '@mdi/js'

const { user, logOut } = useAuthStore()
let menu: Menu

switch (user?.entity_type) {
  /**
   * Клиент
   */
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
        icon: mdiCreditCardCheckOutline,
        title: 'Оплата обучения',
        to: '/billing',
      },
    ]
    break

  /**
   * Учитель
   */
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
        icon: mdiFileDocumentEditOutline,
        title: 'Отчёты',
        to: '/reports',
      },
      {
        icon: mdiNumeric5BoxMultiple,
        title: 'Оценки',
        to: '/grades',
      },
      {
        icon: mdiCurrencyRub,
        title: 'Баланс',
        to: '/balance',
      },
      {
        icon: mdiFileSign,
        title: 'Инструкции',
        to: '/instructions',
      },
    ]
    break

  /**
   * Администратор
   */
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
        icon: mdiBookOpenOutline,
        title: 'Темы',
        to: '/topics',
      },
      {
        icon: mdiFileDocumentEditOutline,
        title: 'Отчёты',
        to: '/reports',
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
        icon: mdiSendCircle,
        title: 'Телеграм',
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
      },
      {
        icon: mdiCalendarBadge,
        title: 'События',
        to: '/events',
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
    ]
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
    <v-list-item v-if="user?.entity_type === EntityType.user" @click="logOut()">
      <template #prepend>
        <v-icon :icon="mdiLogout" />
      </template>
      Выход
    </v-list-item>
    <v-list-item v-else-if="user" :to="{ name: 'profile' }">
      <template #prepend>
        <UiAvatar :item="user" :size="26" />
      </template>
      {{ formatName(user) }}
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

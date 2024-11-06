<script setup lang="ts">
import {
  mdiAccount,
  mdiAccountGroup,
  mdiCalendar,
  mdiCash,
  mdiCurrencyRub,
  mdiFileDocumentEditOutline,
  mdiFileSign,
  mdiNumeric5BoxMultiple,
  mdiStarBoxOutline,
} from '@mdi/js'

const { user } = useAuthStore()

const menu: Menu = [
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
    icon: mdiStarBoxOutline,
    title: 'Отзывы',
    to: '/client-reviews',
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
    icon: mdiCash,
    title: 'Платежи',
    to: '/payments',
  },
  {
    icon: mdiFileSign,
    title: 'Инструкции',
    to: '/instructions',
  },
]

// Классный руководитель
const headTeacherMenu: Menu = [
  {
    icon: mdiAccount,
    title: 'Ученики',
    to: '/clients',
  },
  {
    icon: mdiFileDocumentEditOutline,
    title: 'Отчёты',
    to: '/head-teacher-reports',
  },
]
</script>

<template>
  <v-list nav density="compact">
    <MenuList :items="menu" />
    <template v-if="user?.is_head_teacher">
      <!--      <v-divider /> -->
      <!--      <v-list-subheader> -->
      <!--        Классный руководитель -->
      <!--      </v-list-subheader> -->
      <MenuList :items="headTeacherMenu" />
    </template>
    <v-list-item v-if="user" :to="{ name: 'profile' }">
      <template #prepend>
        <UiAvatar :item="user" :size="26" />
      </template>
      {{ formatName(user) }}
    </v-list-item>
  </v-list>
</template>

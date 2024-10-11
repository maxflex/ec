<script setup lang="ts">
import {
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
</script>

<template>
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
    <v-list-item v-if="user" :to="{ name: 'profile' }">
      <template #prepend>
        <UiAvatar :item="user" :size="26" />
      </template>
      {{ formatName(user) }}
    </v-list-item>
  </v-list>
</template>

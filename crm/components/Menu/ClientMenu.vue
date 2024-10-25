<script setup lang="ts">
import {
  mdiAccountGroup,
  mdiCalendar,
  mdiCreditCardCheckOutline,
  mdiDotsTriangle,
  mdiFileDocumentEditOutline,
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
</script>

<template>
  <v-list nav density="compact">
    <MenuList :items="menu" />
    <v-list-item v-if="user" :to="{ name: 'profile' }">
      <template #prepend>
        <UiAvatar :item="user" :size="26" />
      </template>
      {{ formatName(user) }}
    </v-list-item>
  </v-list>
</template>

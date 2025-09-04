<script setup lang="ts">
import {
  mdiBookOpenBlankVariantOutline,
  mdiCalendar,
  mdiCreditCardCheckOutline,
  mdiDotsTriangle,
  mdiFileDocumentEditOutline,
  mdiNumeric5BoxMultiple,
  mdiTicket,
} from '@mdi/js'

const { user } = useAuthStore()

const menu: Menu = [
  {
    icon: mdiBookOpenBlankVariantOutline,
    title: 'Дневник',
    to: '/journal',
  },
  {
    icon: mdiCalendar,
    title: 'Расписание',
    to: '/schedule',
  },
  {
    icon: mdiNumeric5BoxMultiple,
    title: 'Оценки',
    to: '/grades',
    hide: !user?.has_grades,
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
]
</script>

<template>
  <v-list nav density="compact">
    <MenuList :items="menu.filter(e => !e.hide)" />
    <v-list-item v-if="user" :to="{ name: 'profile' }">
      <template #prepend>
        <UiAvatar :item="user" :size="26" />
      </template>
      {{ formatName(user) }}
    </v-list-item>
  </v-list>
</template>

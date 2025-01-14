<script setup lang="ts">
import {
  mdiAccountGroup,
  mdiCalendar,
  mdiCash,
  mdiCurrencyRub,
  mdiFileDocumentEditOutline,
  mdiFileSign,
  mdiHumanMaleBoard,
  mdiNumeric5BoxMultiple,
  mdiStarBoxOutline,
  mdiTrophy,
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
    count: 'schedule',
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
    icon: mdiTrophy,
    title: 'Стипендия',
    to: '/scholarship-scores',
  },
  {
    icon: mdiFileSign,
    title: 'Инструкции',
    to: '/instructions',
  },
]

if (user?.is_head_teacher) {
  menu.push({
    icon: mdiHumanMaleBoard,
    title: 'Классрук',
    items: [
      {
        title: 'Ученики',
        to: '/clients',
      },
      {
        title: 'Отчёты КР',
        to: '/head-teacher-reports',
      },
    ],
  })
}

nextTick(updateMenuCounts)
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

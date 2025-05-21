<script setup lang="ts">
import {
  mdiAccountGroup,
  mdiCalendar,
  mdiCash,
  mdiCurrencyRub,
  mdiFileDocumentEditOutline,
  mdiFileSign,
  mdiHumanMaleBoard,
  mdiLandPlots,
  mdiNumeric5BoxMultiple,
  mdiStarBoxOutline,
  mdiTicket,
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
  },
  {
    icon: mdiFileDocumentEditOutline,
    title: 'Отчёты',
    to: '/reports',
    count: 'reports',
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
  {
    icon: mdiFileSign,
    title: 'Инструкции проверка',
    to: '/instructions-check',
    hide: user?.id !== 24350,
  },
  {
    icon: mdiLandPlots,
    title: 'Тесты',
    to: '/tests',
  },
  {
    icon: mdiTicket,
    title: 'События',
    to: '/events',
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
    <MenuList :items="menu.filter(e => !e.hide)" />
    <v-list-item v-if="user" :to="{ name: 'profile' }">
      <template #prepend>
        <UiAvatar :item="user" :size="26" />
      </template>
      {{ formatName(user) }}
    </v-list-item>
  </v-list>
</template>

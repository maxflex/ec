<script setup lang="ts">
import {
  mdiBookOpenBlankVariantOutline,
  mdiCalendar,
  mdiFileDocumentEditOutline,
  mdiNumeric5BoxMultiple,
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
]
</script>

<template>
  <v-bottom-navigation grow tag="footer">
    <v-btn
      v-for="m in menu"
      :key="m.to"
      :ripple="false"
      :icon="m.icon"
      :to="m.to"
      :exact="false"
    >
    </v-btn>
    <v-btn v-if="user" to="/profile">
      <UiAvatar :item="user" :size="26" />
    </v-btn>
  </v-bottom-navigation>
</template>

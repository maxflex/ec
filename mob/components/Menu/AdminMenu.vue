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
  mdiSeatPassenger,
  mdiSendCircle,
  mdiStarBoxOutline,
} from '@mdi/js'

const { user } = useAuthStore()

const menu: Menu = [
  {
    icon: mdiAccountGroup,
    title: 'Клиенты',
    to: '/clients',
  },
]

nextTick(updateMenuCounts)
</script>

<template>
  <v-bottom-navigation
    grow
    tag="footer"
    :height="50"
  >
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

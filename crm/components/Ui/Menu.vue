<script setup lang="ts">
import { ENTITY_TYPE } from "~/utils/sment"
import {
  mdiInbox,
  mdiAccount,
  mdiAccountGroup,
  mdiFileDocumentOutline,
  mdiCalendar,
  mdiPrinter,
  mdiDotsTriangle,
  mdiLogout,
} from "@mdi/js"

const { user, logOut, clearCurrentToken } = useAuthStore()
let menu: Menu

switch (user?.entity_type) {
  case ENTITY_TYPE.client:
    menu = [
      {
        icon: mdiAccountGroup,
        title: "Группы",
        to: "/groups",
      },
      {
        icon: mdiDotsTriangle,
        title: "Тесты",
        to: "/tests",
      },
    ]
    break

  case ENTITY_TYPE.teacher:
    menu = [
      {
        icon: mdiAccountGroup,
        title: "Группы",
        to: "/groups",
      },
    ]
    break

  default:
    menu = [
      {
        icon: mdiInbox,
        title: "Заявки",
        to: "/requests",
      },
      {
        icon: mdiAccount,
        title: "Клиенты",
        to: "/clients",
      },
      {
        icon: mdiAccountGroup,
        title: "Группы",
        to: "/groups",
      },
      {
        icon: mdiFileDocumentOutline,
        title: "Договоры",
        to: "/contracts",
      },
      {
        icon: mdiCalendar,
        title: "Праздники",
        to: "/vacations",
      },
      {
        icon: mdiPrinter,
        title: "Макросы",
        to: "/macros",
      },
      {
        icon: mdiDotsTriangle,
        title: "Тесты",
        to: "/tests",
      },
    ]
}

async function exit() {
  await logOut()
  clearCurrentToken()
  const path = sessionStorage.getItem("redirect") || "/"
  window.location.href = path
}
</script>

<template>
  <div class="logo">
    <img src="/img/logo.svg" />
    <h3>ЕГЭ-Центр</h3>
  </div>
  <v-list nav density="compact">
    <v-list-item v-for="{ title, to, icon } in menu" :key="title" :to="to">
      <template #prepend>
        <v-icon :icon="icon"></v-icon>
      </template>
      {{ title }}
    </v-list-item>
    <v-list-item @click="exit()">
      <template #prepend>
        <v-icon :icon="mdiLogout"></v-icon>
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

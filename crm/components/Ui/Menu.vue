<script setup lang="ts">
import {
  mdiInbox,
  mdiAccount,
  mdiAccountGroup,
  mdiFileDocumentOutline,
} from "@mdi/js"

const menu: Array<{
  to: string
  title: string
  icon: string
}> = [
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
]
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

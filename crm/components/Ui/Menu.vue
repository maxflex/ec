<script setup lang="ts">
const menu: Array<{
  to: string
  title: string
  icon: string
}> = [
  {
    icon: "mdi-inbox",
    title: "Заявки",
    to: "/requests",
  },
  {
    icon: "mdi-account",
    title: "Клиенты",
    to: "/clients",
  },
  {
    icon: "mdi-account-group",
    title: "Группы",
    to: "/groups",
  },
  {
    icon: "mdi-file-document-outline",
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

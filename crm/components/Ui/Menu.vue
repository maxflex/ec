<script setup lang="ts">
const menu: Array<{
  to: string
  title: string
}> = [
  {
    title: "Заявки",
    to: "/requests",
  },
  {
    title: "Менты",
    to: "/ment",
  },
]
</script>

<template>
  <div class="logo">
    <img src="/img/logo.svg" />
    <h2>ЕГЭ-Центр</h2>
  </div>
  <v-list nav density="compact">
    <v-list-item v-for="{ title, to } in menu" :key="title" :to="to">
      <template #prepend>
        <v-icon> view_cozy </v-icon>
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
    margin-right: 16px
</style>

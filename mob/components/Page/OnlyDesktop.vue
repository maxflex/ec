<script setup lang="ts">
const { logOut } = useAuthStore()
const { $isTgMiniApp } = useNuxtApp()

const loading = ref(false)

function doLogout() {
  loading.value = true
  logOut()
}
</script>

<template>
  <div class="fullscreen-message">
    <p>
      Для сотрудников и преподавателей ЕГЭ-Центра личный кабинет доступен только на компьютере 😔
      <br />
      <br />
      <v-btn v-if="!$isTgMiniApp" block color="primary" :loading="loading" @click="doLogout()">
        выйти
      </v-btn>
    </p>
  </div>
</template>

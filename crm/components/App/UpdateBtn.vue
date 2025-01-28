<script setup lang="ts">
const { $addSseListener } = useNuxtApp()
const isUpdateAvailable = ref(false)
const loading = ref(false)
const resetFiltersKeys = ref<string[]>([])

$addSseListener('AppUpdatedEvent', (data) => {
  console.log('data', data)
  resetFiltersKeys.value = data
  isUpdateAvailable.value = true
})

function updateApp() {
  loading.value = true
  setTimeout(() => location.reload(), 1000)
}
</script>

<template>
  <v-slide-y-reverse-transition>
    <div v-if="isUpdateAvailable" class="app-update-btn">
      <v-btn color="primary" rounded="lg" :loading="loading" @click="updateApp()">
        обновить приложение
      </v-btn>
    </div>
  </v-slide-y-reverse-transition>
</template>

<style lang="scss">
.app-update-btn {
  position: sticky;
  bottom: 0;
  background: white;
  width: 100%;
  padding-bottom: 8px;
  button {
    scale: 0.9;
  }
}
</style>

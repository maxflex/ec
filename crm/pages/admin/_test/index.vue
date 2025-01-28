<script setup lang="ts">
const { showGlobalMessage } = useGlobalMessage()

function showMessage(color: GlobalMessageColor) {
  showGlobalMessage(
    color === 'error'
      ? 'Ошибка сервера'
      : 'Успешно'
    , color,
  )
}
</script>

<template>
  <div class="pa-10 d-flex ga-6">
    <v-btn color="primary" @click="showMessage('error')">
      error
    </v-btn>
    <v-btn color="primary" @click="showMessage('success')">
      success
    </v-btn>
  </div>
</template>

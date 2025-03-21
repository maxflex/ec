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
    <UiLoader />
  </div>
</template>

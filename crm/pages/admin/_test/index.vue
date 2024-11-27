<script setup lang="ts">
const { showGlobalMessage } = useGlobalMessage()

function showMessage() {
  showGlobalMessage('Ошибка сервера', 'error')
}
</script>

<template>
  <div class="pa-10">
    <v-btn color="primary" @click="showMessage()">
      show message
    </v-btn>
  </div>
</template>

<style scoped lang="scss">

</style>

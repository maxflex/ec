<script setup lang="ts">
/**
 * АВТОРИЗАЦИЯ В CALL-APP
 */

const isLocalhost = useIsLocalhost()
const token = ref()
const isFinished = ref(false)
const isCopiedToClipboard = ref(false)

function openCallApp() {
  console.log('auth-token', token.value)
  window.location.href = `callapp://auth/callback?token=${token.value}`
}

function getToken() {
  token.value = useCookie('token').value
}

function copyToClipboard() {
  isCopiedToClipboard.value = true
  navigator.clipboard.writeText(token.value)
}

onMounted(() => {
  getToken()
  if (!isLocalhost) {
    setTimeout(openCallApp, 1000)
    setTimeout(() => isFinished.value = true, 6000)
  }
})
</script>

<template>
  <UiNoData v-if="isLocalhost">
    <v-text-field :model-value="token" readonly :width="630" />
    <v-btn color="primary" :width="200" :disabled="isCopiedToClipboard" @click="copyToClipboard()">
      скопировать
    </v-btn>
  </UiNoData>
  <UiNoData v-else-if="isFinished">
    вы можете закрыть эту страницу
  </UiNoData>
  <UiLoader v-else>
    открываю CallApp...
  </UiLoader>
</template>

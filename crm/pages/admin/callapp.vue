<script setup lang="ts">
/**
 * АВТОРИЗАЦИЯ В CALL-APP
 */

const { public: config } = useRuntimeConfig()
const isLocalhost = config.env === 'local'
const token = ref()

function openCallApp() {
  console.log('auth-token', token.value)
  window.location.href = `callapp://auth/callback?token=${token.value}`
}

function getToken() {
  token.value = useCookie('token').value
}

onMounted(() => {
  getToken()
  if (!isLocalhost) {
    setTimeout(openCallApp, 1000)
  }
})
</script>

<template>
  <pre v-if="isLocalhost" style="height: 100vh; width: 100%; display: flex; align-items: center; justify-content: center;">
    {{ token }}
  </pre>
  <UiLoader v-else>
    открываю CallApp...
  </UiLoader>
</template>

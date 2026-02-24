<script setup lang="ts">
/**
 * АВТОРИЗАЦИЯ В CALL-APP
 */

function openCallApp() {
  const token = useCookie('token').value
  console.log('auth-token', token)
  window.location.href = `callapp://auth/callback?token=${token}`
}

onMounted(() => {
  setTimeout(openCallApp, 5000)
})
</script>

<template>
  <UiLoader>
    открываю CallApp...
  </UiLoader>
</template>

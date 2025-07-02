<script setup lang="ts">
import { useBackButton, useMiniApp } from 'vue-tg'

const { logInAndRemember } = useAuthStore()

// выполняется авто-вход...
const autoLogIn = ref(true)

const loading = ref(false)
const isError = ref(false)

async function auth() {
  loading.value = true
  const { data, error } = await useHttp<TokenResponse>(
    `pub/auth/via-telegram`,
    {
      method: 'post',
      body: {
        initData: useMiniApp().initData,
      },
    },
  )

  if (error.value) {
    autoLogIn.value = false
    isError.value = true
    loading.value = false
    return
  }

  logInAndRemember(data.value!)
}

useBackButton().hide!()

nextTick(auth)
</script>

<template>
  <div class="fullscreen-message">
    <img v-if="autoLogIn" src="/img/logo-gray.svg" />
    <p v-else>
      Не получается войти в приложение 😔
      <br />
      <br />
      Обратитесь, пожалуйста, в учебную часть, и мы всё исправим.
    </p>
  </div>
</template>

<style lang="scss">
.fullscreen-message {
  img {
    width: 50px;
    animation-name: tgMiniAppLoading;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
    animation-duration: 2s;
  }
}

@keyframes tgMiniAppLoading {
  from {
    opacity: 0.8;
  }
  50% {
    opacity: 0.5;
  }
  to {
    opacity: 0.8;
  }
}
</style>

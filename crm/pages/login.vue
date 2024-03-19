<script setup>
import Pusher from "pusher-js"

const { $store } = useNuxtApp()
const form = reactive({ phone: "" })
const window = ref(0)
const phoneMask = { mask: "+7 (###) ###-##-##" }
const user = ref()
const loading = ref(false)
const errors = ref({})
const otpInput = ref()
const otp = reactive({
  code: "",
  error: null,
  loading: false,
})
let pusher, channel

const cookieToken = useCookie("token", { maxAge: 60 * 60 * 24 * 1000 })
console.log(cookieToken.value)

const onPhoneEnter = async () => {
  loading.value = true
  errors.value = {}
  const { data, error } = await useHttp("auth/login", {
    method: "post",
    body: { ...form },
  })
  setTimeout(() => (loading.value = false), 300)
  if (error.value) {
    errors.value = error.value.data.errors
    return
  }
  user.value = data.value
  console.log(user.value)
  window.value = user.value.telegram_id === null ? 1 : 2
}

function initPusher() {
  pusher = new Pusher("30dcae8449659255e169", {
    cluster: "eu",
  })
  channel = pusher.subscribe("auth." + user.value.id)
}

function logIn() {
  $store.user = user.value
  cookieToken.value = [user.value.id, user.value.entity_type].join("|")
  navigateTo({ name: "index" })
}

watch(
  () => window.value,
  (newVal, oldVal) => {
    console.log(`${oldVal} => ${newVal}`)
    if (newVal === 1) {
      initPusher()
      channel.bind("App\\Events\\AuthNumberVerified", ({ user }) => {
        console.log(user)
        user.value = user
        logIn()
      })
    }
    if (newVal === 2) {
      setTimeout(() => otpInput.value.focus(), 300)
    }
  },
)

async function onOtpFinish() {
  otp.error = false
  otp.loading = true
  const { data, error } = await useHttp("auth/verify-code", {
    method: "post",
    body: {
      code: otp.code,
    },
  })
  if (error.value) {
    otp.loading = false
    nextTick(() => otpInput.value.focus())
    return
  }
  if (data.value.verified) {
    logIn()
  } else {
    otp.loading = false
    nextTick(() => {
      otp.error = true
      otpInput.value.focus()
    })
  }
}

onUnmounted(() => {
  console.log("deactivated")
  channel?.unbind()
  pusher?.unsubscribe("auth")
})

definePageMeta({
  layout: "login",
})
</script>

<template>
  <form class="login">
    <div class="login__logo">
      <img src="/img/logo.svg" />
    </div>
    <v-window v-model="window" class="login__content">
      <v-window-item>
        <v-text-field
          v-model="form.phone"
          label="Телефон"
          :error-messages="errors.phone"
          @keydown.enter="onPhoneEnter()"
          v-maska:[phoneMask]
        />
        <v-btn
          color="primary"
          :loading="loading"
          block
          size="x-large"
          @click="onPhoneEnter()"
        >
          Войти
        </v-btn>
      </v-window-item>
      <v-window-item eager>
        <div class="login__info" v-if="user">
          <div class="login__info__title">
            {{ user.entity.first_name }}, здравствуйте!
          </div>
          <div>
            Это ваш первый вход в ЛК. Для продолжения необходимо добавить бота в
            Telegram
          </div>
        </div>
        <div class="login__qr">
          <img src="/img/qr.jpg" />
        </div>
        <!-- <a href="https://t.me/egecentr_bot" target="_blank">
            <v-btn color="primary" block size="x-large"> Открыть Telegram </v-btn>
          </a> -->
      </v-window-item>
      <v-window-item eager>
        <div class="login__info">
          <div class="login__info__title">Проверьте Telegram</div>
          <div>Введите код, который пришёл к вам в сообщения</div>
        </div>
        <v-otp-input
          :disabled="otp.loading"
          :error="otp.error"
          ref="otpInput"
          :length="5"
          v-model="otp.code"
          @finish="onOtpFinish"
          class="mt-5"
          width="300"
        ></v-otp-input>
        <v-btn
          color="primary"
          :loading="otp.loading"
          block
          size="x-large"
          @click="onOtpFinish()"
        >
          Войти
        </v-btn>
      </v-window-item>
    </v-window>
  </form>
</template>

<style lang="scss">
.login {
  background: white;
  border-radius: 20px;
  width: 400px;
  border: 2px solid rgba(255, 196, 35, 0.5);
  & > .v-text-field {
    margin-bottom: 20px;
    // .v-field__outline {
    //   color: rgb(var(--v-theme-primary));
    //   label {
    //     color: rgba(
    //       var(--v-theme-on-background),
    //       var(--v-high-emphasis-opacity)
    //     ) !important;
    //   }
    // }
  }
  button {
    margin-top: 60px;
  }
  &__logo {
    text-align: center;
    padding: 30px 0;
    img {
      width: 80px;
    }
  }
  &__content {
    padding: 30px;
  }
  &__qr {
    margin-top: 40px;
    text-align: center;
    img {
      width: 120px;
    }
  }
  &__info {
    font-size: 20px;
    text-align: center;
    text-wrap: balance;
    &__title {
      font-weight: bold;
      margin-bottom: 20px;
    }
  }
}
</style>

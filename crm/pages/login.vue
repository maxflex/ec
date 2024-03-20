<script setup lang="ts">
import Pusher, { Channel } from "pusher-js"

const { $store } = useNuxtApp()
const {
  public: { pusherAppKey },
} = useRuntimeConfig()
const phone = ref("")
const window = ref(0)
const phoneMask = { mask: "+7 (###) ###-##-##" }
const user = ref<User>()
const loading = ref(false)
const errors = ref<{
  phone?: ResponseErrors
  code?: ResponseErrors
}>({})
const otpInput = ref()
const otp = reactive({
  code: "",
  error: false,
  loading: false,
})
let pusher: Pusher
let channel: Channel

const cookieToken = useCookie("token", { maxAge: 60 * 60 * 24 * 1000 })
console.log(cookieToken.value)

const onPhoneEnter = async () => {
  loading.value = true
  errors.value = {}
  const { data, error } = await useHttp<User>("auth/login", {
    method: "post",
    body: { phone: phone.value },
  })
  setTimeout(() => (loading.value = false), 300)
  if (error.value) {
    errors.value = error.value.data.errors
    return
  }
  if (data.value) {
    user.value = data.value
    console.log(user.value)
    nextTick(() => {
      window.value = user.value?.telegram_id ? 2 : 1
    })
  }
}

function initPusher() {
  pusher = new Pusher(pusherAppKey, { cluster: "eu" })
  channel = pusher.subscribe("auth." + user.value?.id)
}

function logIn() {
  if (user.value) {
    $store.user = user.value
    cookieToken.value = [user.value.id, user.value.entity_type].join("|")
    navigateTo({ name: "index" })
  }
}

watch(
  () => window.value,
  (newVal, oldVal) => {
    console.log(`${oldVal} => ${newVal}`)
    if (newVal === 1) {
      initPusher()
      channel.bind(
        "App\\Events\\AuthNumberVerified",
        (data: { user: User }) => {
          console.log(data.user)
          user.value = data.user
          logIn()
        },
      )
    }
    if (newVal === 2) {
      setTimeout(() => otpInput.value.focus(), 300)
    }
  },
)

async function onOtpFinish() {
  errors.value = {}
  otp.loading = true
  const { data, error } = await useHttp("auth/verify-code", {
    method: "post",
    body: {
      phone: phone.value,
      code: otp.code,
    },
  })
  if (error.value) {
    otp.loading = false
    errors.value = error.value.data.errors
    nextTick(() => otpInput.value.focus())
    return
  }
  logIn()
}

onUnmounted(() => {
  console.log("deactivated")
  channel?.unbind()
  pusher?.unsubscribe("auth")
})

definePageMeta({ layout: "login" })
</script>

<template>
  <form class="login">
    <div class="login__logo">
      <img src="/img/logo.svg" />
    </div>
    <v-window
      v-model="window"
      :reverse="user?.telegram_id ? true : false"
      class="login__content"
    >
      <v-window-item>
        <v-text-field
          v-model="phone"
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
          :error="!!errors.code"
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

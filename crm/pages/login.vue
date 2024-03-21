<script setup lang="ts">
import Pusher, { Channel } from "pusher-js"

const { $store } = useNuxtApp()
const { public: config } = useRuntimeConfig()
const phone = ref("")
const phoneMask = { mask: "+7 (###) ###-##-##" }
const user = ref<User>()
const loading = ref(false)
const errors = ref<{
  phone?: ResponseErrors
  code?: ResponseErrors
}>({})
const otpInput = ref()
const phoneInput = ref()
const otp = reactive({
  code: "",
  error: false,
  loading: false,
})
let pusher: Pusher
let channel: Channel
const cookieToken = useCookie("token", { maxAge: 60 * 60 * 24 * 1000 })
const cookieUser = useCookie<User | null>("user", {
  maxAge: 60 * 60 * 24 * 1000,
})
const window = ref(cookieUser.value ? 1 : 0)

const onPhoneEnter = async () => {
  loading.value = true
  errors.value = {}
  const { data, error } = await useHttp<User>("auth/login", {
    method: "post",
    body: {
      phone: phone.value,
    },
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
      window.value = user.value?.telegram_id ? 3 : 2
    })
  }
}

function initPusher() {
  pusher = new Pusher(config.pusherAppKey, { cluster: "eu" })
  channel = pusher.subscribe("auth." + user.value?.id)
}

function auth(token: string, user: User) {
  $store.user = user
  cookieToken.value = token
  if (user.entity_type !== ENTITY_TYPE.teacher) {
    cookieUser.value = user
  }
  const name = sessionStorage.getItem("redirect") || "index"
  navigateTo({ name })
}

function clearCookieUser() {
  window.value = 0
  // setTimeout(() => (cookieUser.value = null), 1000)
}

function continueCookieUser() {
  if (cookieUser.value) {
    phone.value = cookieUser.value.number
    onPhoneEnter()
  }
}

async function onOtpFinish() {
  errors.value = {}
  otp.loading = true
  const { data, error } = await useHttp<TokenResponse>("auth/verify-code", {
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
  if (data.value) {
    const { token, user } = data.value
    auth(token, user)
  }
}

watch(
  () => window.value,
  (newVal, oldVal) => {
    console.log(`${oldVal} => ${newVal}`)
    if (newVal === 0) {
      setTimeout(() => phoneInput.value.focus(), 300)
    }
    if (newVal === 2) {
      initPusher()
      channel.bind(
        "App\\Events\\TelegramBotAdded",
        ({ token, user }: TokenResponse) => auth(token, user),
      )
    }
    if (newVal === 3) {
      setTimeout(() => otpInput.value.focus(), 300)
    }
  },
)

onUnmounted(() => {
  console.log("deactivated")
  channel?.unbind()
  pusher?.unsubscribe("auth")
})

onMounted(() => {
  if (window.value === 0) {
    phoneInput.value?.focus()
  }
})

definePageMeta({ layout: "login" })
</script>

<template>
  <form class="login">
    <div class="login__logo">
      <img src="/img/logo.svg" />
    </div>
    <v-window v-model="window" class="login__content">
      <v-window-item>
        <v-text-field
          v-model="phone"
          label="Телефон"
          :error-messages="errors.phone"
          @keydown.enter="onPhoneEnter()"
          ref="phoneInput"
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
        <v-card
          title="Card title"
          subtitle="Subtitle"
          variant="tonal"
          v-if="cookieUser"
        >
          <template #title>
            {{ formatName(cookieUser) }}
          </template>
          <template #subtitle>
            {{ formatPhone(cookieUser.number) }}
          </template>
          <template #prepend>
            <UserAvatar :user="cookieUser" class="mr-3" />
          </template>
        </v-card>
        <v-btn
          color="primary"
          :loading="loading"
          block
          size="x-large"
          @click="continueCookieUser()"
        >
          продолжить
        </v-btn>
        <div class="login__other">
          <span @click="clearCookieUser()">другой пользователь</span>
        </div>
      </v-window-item>
      <v-window-item eager>
        <div class="login__info" v-if="user">
          <div class="login__info-title">
            {{ user.first_name }}, здравствуйте!
          </div>
          <div>
            Это ваш первый вход в ЛК. Для продолжения необходимо добавить бота в
            Telegram
          </div>
        </div>
        <div class="login__qr">
          <img
            :src="config.env === 'local' ? '/img/qr-local.png' : '/img/qr.jpg'"
          />
        </div>
        <!-- <a href="https://t.me/egecentr_bot" target="_blank">
            <v-btn color="primary" block size="x-large"> Открыть Telegram </v-btn>
          </a> -->
      </v-window-item>
      <v-window-item eager>
        <div class="login__info">
          <div class="login__info-title">Проверьте Telegram</div>
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
      <!-- reverse transition fix -->
      <v-window-item></v-window-item>
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
    &-title {
      font-weight: bold;
      margin-bottom: 20px;
    }
  }
  &__other {
    text-align: center;
    position: relative;
    width: 100%;
    top: 12px;
    span {
      cursor: pointer;
      color: rgb(var(--v-theme-gray));
      transition: color cubic-bezier(0.4, 0, 0.2, 1) 0.15s;
      &:hover {
        color: black;
      }
    }
  }
  .v-card__underlay {
    background: rgb(var(--v-theme-gray)) !important;
    // opacity: 1 !important;
  }
  .v-card-title {
    font-size: 18px !important;
  }
}
</style>

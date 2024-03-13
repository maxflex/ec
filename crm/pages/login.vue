<script setup>
const { $store } = useNuxtApp()
const form = reactive({
  phone: "",
  password: "",
})

const phoneMask = { mask: "+7 (###) ###-##-##" }

let loading = ref(false)
let errors = ref({})

const cookieToken = useCookie("token", {
  maxAge: 60 * 60 * 24 * 1000,
})
console.log(cookieToken.value)

const submit = async () => {
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
  const user = data.value
  console.log(user)
  $store.user = user
  cookieToken.value = [user.id, user.entity_type].join("|")
  await navigateTo({ name: "index" })
}

definePageMeta({
  layout: "login",
})
</script>

<template>
  <form @submit.prevent="submit()">
    <div class="text-center mb-0">
      <img src="/img/logo.svg" />
    </div>
    <v-text-field
      variant="outlined"
      v-model="form.phone"
      label="Телефон"
      :error-messages="errors.phone"
      v-maska:[phoneMask]
    />
    <v-text-field
      variant="outlined"
      v-model="form.password"
      label="Пароль"
      type="password"
      :error-messages="errors.password"
    />
    <v-btn
      color="primary"
      :loading="loading"
      block
      type="submit"
      size="x-large"
    >
      Войти
    </v-btn>
  </form>
</template>

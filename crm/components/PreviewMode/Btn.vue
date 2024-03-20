<script setup lang="ts">
const { user } = defineProps<{ user: User }>()
const { $store } = useNuxtApp()
const loading = ref(false)

async function enter() {
  const cookieToken = useCookie("preview")
  loading.value = true
  const { data } = await useHttp<TokenResponse>("auth/preview", {
    method: "post",
    body: user,
  })
  if (data.value) {
    const { token, user } = data.value
    $store.user = user
    cookieToken.value = token
    setTimeout(() => (window.location.href = "/"), 500)
  }
}
</script>

<template>
  <v-btn
    color="gray"
    :size="48"
    icon="$preview"
    variant="plain"
    :loading="loading"
    @click="enter()"
  />
</template>

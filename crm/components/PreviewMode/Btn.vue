<script setup lang="ts">
const { logIn } = useAuthStore()
const { user } = defineProps<{ user: User }>()
const loading = ref(false)

async function enter() {
  loading.value = true
  const { data } = await useHttp<TokenResponse>("preview", {
    method: "post",
    body: user,
  })
  if (data.value) {
    const { token, user } = data.value
    logIn(user, token, true)
  }
}
</script>

<template>
  <v-btn
    :size="48"
    icon="$preview"
    variant="plain"
    :loading="loading"
    @click="enter()"
  />
</template>

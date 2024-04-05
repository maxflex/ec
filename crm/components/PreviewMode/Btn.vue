<script setup lang="ts">
const { logIn } = useAuthStore()
const { user } = defineProps<{ user: User }>()
const loading = ref(false)
const route = useRoute()

async function enter() {
  loading.value = true
  const { data } = await useHttp<TokenResponse>("auth/preview", {
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
    color="gray"
    :size="48"
    icon="$preview"
    variant="plain"
    :loading="loading"
    @click="enter()"
  />
</template>

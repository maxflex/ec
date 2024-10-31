<script setup lang="ts">
const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()
const { logIn } = useAuthStore()
const loading = ref(false)

async function enter() {
  loading.value = true
  const { data } = await useHttp<TokenResponse>(
    `preview`,
    {
      method: 'post',
      body: {
        client_id: clientId,
        teacher_id: teacherId,
      },
    },
  )
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

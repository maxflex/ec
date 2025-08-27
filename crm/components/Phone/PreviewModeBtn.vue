<script setup lang="ts">
const { item } = defineProps<{ item: PhoneResource }>()
const { logIn } = useAuthStore()
const loading = ref(false)

async function enter() {
  loading.value = true
  const { data } = await useHttp<TokenResponse>(
    `preview-mode`,
    {
      method: 'post',
      body: {
        phone_id: item.id,
      },
    },
  )
  const { token, user } = data.value!
  logIn(user, token, true)
}

const isPreviewModeAvailable = [
  EntityTypeValue.client,
  EntityTypeValue.representative,
  EntityTypeValue.teacher,
].includes(item.entity_type)
</script>

<template>
  <v-btn
    :disabled="!isPreviewModeAvailable"
    :size="48"
    icon="$preview"
    variant="text"
    :loading="loading"
    color="black"
    @click="enter()"
  />
</template>

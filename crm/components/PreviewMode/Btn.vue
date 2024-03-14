<script setup lang="ts">
const { user } = defineProps<{ user: User }>()
const { $store } = useNuxtApp()
const loading = ref(false)

async function enter() {
  const cookie = useCookie("preview")
  loading.value = true
  $store.user = user
  cookie.value = [user.id, user.entity_type].join("|")
  setTimeout(() => (window.location.href = "/"), 500)
  // await navigateTo({ name: "index" })
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

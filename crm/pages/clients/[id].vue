<script setup lang="ts">
import type { Client } from "~/utils/models"

const route = useRoute()
const client = ref<Client>()

async function loadData() {
  const { data } = await useHttp(`clients/${route.params.id}`)
  client.value = data.value as Client
}

onMounted(async () => {
  await loadData()
})
</script>

<template>
  <h3 class="page-title">Клиент {{ route.params.id }}</h3>
  <pre>{{ client }}</pre>
</template>

<script setup lang="ts">
import type { ClientResource } from '~/components/Client'

const route = useRoute()

const clientId = route.query.client_id
const year: Year = route.query.year as unknown as Year
const client = ref<ClientResource>()

async function loadClient() {
  const { data } = await useHttp<ClientResource>(`clients/${clientId}`)
  client.value = data.value!
}

nextTick(loadClient)
</script>

<template>
  <ScheduleDraftEditor v-if="client" :client="client" :year="year" />
  <UiLoader v-else />
</template>

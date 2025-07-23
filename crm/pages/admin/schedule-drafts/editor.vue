<script setup lang="ts">
import type { ClientResource } from '~/components/Client'

const route = useRoute()

const clientId = route.query.client_id
const year: Year = route.query.year as unknown as Year

const client = ref<ClientResource>()
const contract = ref<ContractResource>()

async function loadClient() {
  const id = clientId || contract.value?.client_id
  const { data } = await useHttp<ClientResource>(`clients/${id}`)
  client.value = data.value!
}

nextTick(loadClient)
</script>

<template>
  <ScheduleDraftEditor v-if="client" :client="client" :year="year" />
  <UiLoader v-else />
</template>

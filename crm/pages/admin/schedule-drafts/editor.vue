<script setup lang="ts">
import type { ClientResource } from '~/components/Client'

const route = useRoute()

const clientId = route.query.client_id
const contractId = route.query.contract_id

const client = ref<ClientResource>()
const contract = ref<ContractResource>()

async function loadClient() {
  const id = clientId || contract.value?.client_id
  const { data } = await useHttp<ClientResource>(`clients/${id}`)
  client.value = data.value!
}

async function loadContract() {
  const { data } = await useHttp<ContractResource>(`contracts/${contractId}`)
  contract.value = data.value!
  loadClient()
}

nextTick(clientId ? loadClient : loadContract)
</script>

<template>
  <ScheduleDraftEditor v-if="client" :client="client" :contract-id="contract?.id" />
  <UiLoader v-else />
</template>

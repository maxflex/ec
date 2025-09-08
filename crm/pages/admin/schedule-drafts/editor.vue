<script setup lang="ts">
import type { ClientResource } from '~/components/Client'
import type { SavedScheduleDraftResource } from '~/components/ScheduleDraft'
import { apiUrl } from '~/components/ScheduleDraft'

const route = useRoute()

const { id, client_id: clientId } = route.query
const client = ref<PersonResource>()
const savedDraft = ref<SavedScheduleDraftResource>()

async function loadClient() {
  const { data } = await useHttp<ClientResource>(`clients/${clientId}`)
  client.value = data.value!
}

async function loadData() {
  const { data } = await useHttp<SavedScheduleDraftResource>(`${apiUrl}/${id}`)
  if (data.value) {
    savedDraft.value = data.value
    client.value = data.value.client
  }
}

nextTick(id ? loadData : loadClient)
</script>

<template>
  <ScheduleDraftEditor v-if="client" :client="client" :saved-draft="savedDraft" />
  <UiLoader v-else />
</template>

<script setup lang="ts">
import type { ClientResource } from '~/components/Client'
import type { SavedProjectResource } from '~/components/Project'
import { apiUrl } from '~/components/Project'

const route = useRoute()

const { id, client_id: clientId } = route.query
const client = ref<PersonResource>()
const savedProject = ref<SavedProjectResource>()

async function loadClient() {
  const { data } = await useHttp<ClientResource>(`clients/${clientId}`)
  client.value = data.value!
}

async function loadData() {
  const { data } = await useHttp<SavedProjectResource>(`${apiUrl}/${id}`)
  if (data.value) {
    savedProject.value = data.value
    client.value = data.value.client
  }
}

nextTick(id ? loadData : loadClient)
</script>

<template>
  <ProjectEditor v-if="client" :client="client" :saved-project="savedProject" />
  <UiLoader v-else />
</template>

<script setup lang="ts">
import type { ClientResource } from '~/components/Client'
import type { ProjectResource } from '~/components/Project'
import { apiUrl } from '~/components/Project'

const route = useRoute()
const loading = ref(true)
const { id, client_id: clientId } = route.query
const client = ref<PersonResource>()
const savedProject = ref<ProjectResource>()

async function loadClient() {
  const { data } = await useHttp<ClientResource>(`clients/${clientId}`)
  client.value = data.value!
}

async function loadProject() {
  const { data } = await useHttp<ProjectResource>(`${apiUrl}/${id}`)
  savedProject.value = data.value!
  client.value = data.value!.client
}

nextTick(async () => {
  if (id) {
    await loadProject()
  }
  else if (clientId) {
    await loadClient()
  }
  loading.value = false
})
</script>

<template>
  <UiLoader v-if="loading" />
  <ProjectEditor v-else :client="client" :saved-project="savedProject" />
</template>

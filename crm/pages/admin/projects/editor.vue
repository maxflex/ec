<script setup lang="ts">
import type { ClientResource } from '~/components/Client'
import type { ProjectResource } from '~/components/Project'
import { apiUrl } from '~/components/Project'

const route = useRoute()
const loading = ref(true)
const { id, client_id: clientId } = route.query
const client = ref<ClientResource>()
const savedProject = ref<ProjectResource>()

async function loadClient() {
  const { data } = await useHttp<ClientResource>(`clients/${clientId}`)
  client.value = data.value!
}

async function loadProject() {
  const { data } = await useHttp<ProjectResource>(`${apiUrl}/${id}`)
  if (data.value) {
    savedProject.value = data.value
    if (data.value.client) {
      client.value = data.value!.client
    }
  }
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
  <ProjectEditor v-if="!loading" :client="client" :saved-project="savedProject" />
</template>

<script setup lang="ts">
import type { ClientResource } from '~/components/Client'
import type { SavedScheduleDraftResource } from '~/components/ScheduleDraft'
import { apiUrl } from '~/components/ScheduleDraft'

const route = useRoute()

const { client_id: clientId, year, id } = route.query
const client = ref<PersonResource>()
const yearProp = ref<Year>(currentAcademicYear())
const savedDraft = ref<SavedScheduleDraftResource>()

async function loadClient() {
  const { data } = await useHttp<ClientResource>(`clients/${clientId}`)
  yearProp.value = year as unknown as Year
  client.value = data.value!
}

async function loadData() {
  const { data } = await useHttp<SavedScheduleDraftResource>(`${apiUrl}/${id}`)
  if (data.value) {
    savedDraft.value = data.value
    yearProp.value = data.value.year
    client.value = data.value.client
  }
}

nextTick(id ? loadData : loadClient)
</script>

<template>
  <ScheduleDraftEditor v-if="client" :client="client" :year="yearProp" :saved-draft="savedDraft" />
  <UiLoader v-else />
</template>

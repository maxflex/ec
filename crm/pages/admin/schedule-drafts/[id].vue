<script setup lang="ts">
import type { SavedScheduleDraftResource } from '~/components/ScheduleDraft'
import { apiUrl } from '~/components/ScheduleDraft'

const route = useRoute()

const id = route.params.id
const savedDraft = ref<SavedScheduleDraftResource>()

async function loadData() {
  const { data } = await useHttp<SavedScheduleDraftResource>(`${apiUrl}/${id}`)
  savedDraft.value = data.value!
}

nextTick(loadData)
</script>

<template>
  <ScheduleDraftEditor
    v-if="savedDraft"
    :client="savedDraft.client"
    :year="savedDraft.year"
    :saved-draft="savedDraft"
  />
  <UiLoader v-else />
</template>

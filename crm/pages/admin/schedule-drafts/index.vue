<script setup lang="ts">
import type { SavedScheduleDraftResource } from '~/components/ScheduleDraft'
import { apiUrl } from '~/components/ScheduleDraft'

const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears } = useIndex<SavedScheduleDraftResource>(
  apiUrl,
  filters,
  {
    loadAvailableYears: true,
  },
)

async function deleteSavedDraft(savedDraft: SavedScheduleDraftResource) {
  const { id } = savedDraft!
  const index = items.value.findIndex(e => e.id === id)
  items.value.splice(index, 1)
  await useHttp(`${apiUrl}/${id}`, { method: 'DELETE' })
  useGlobalMessage(`<b>ID ${id}</b> – проект удалён`, 'success')
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <ScheduleDraftList :items="items" @delete="deleteSavedDraft" />
  </UiIndexPage>
</template>

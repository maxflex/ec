<script setup lang="ts">
import type { SavedScheduleDraftResource } from '~/components/ScheduleDraft'
import { apiUrl } from '~/components/ScheduleDraft'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<SavedScheduleDraftResource>(
  apiUrl,
  filters,
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
      <UiYearSelector v-model="filters.year" density="comfortable" />
    </template>
    <ScheduleDraftList :items="items" @delete="deleteSavedDraft" />
  </UiIndexPage>
</template>

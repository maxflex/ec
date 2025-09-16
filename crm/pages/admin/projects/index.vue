<script setup lang="ts">
import type { SavedProjectResource } from '~/components/Project'
import { apiUrl } from '~/components/Project'

const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears } = useIndex<SavedProjectResource>(
  apiUrl,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      has_problems_in_list: true,
    },
  },
)

async function deleteSavedProject(savedProject: SavedProjectResource) {
  const { id } = savedProject!
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
    <ProjectList :items="items" @delete="deleteSavedProject" />
  </UiIndexPage>
</template>

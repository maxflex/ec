<script setup lang="ts">
import type { ProjectResource } from '~/components/Project'
import { apiUrl } from '~/components/Project'

const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears } = useIndex<ProjectResource>(
  apiUrl,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      with_problems: 1,
    },
  },
)

async function deleteSavedProject(savedProject: ProjectResource) {
  const { id } = savedProject!
  const index = items.value.findIndex(e => e.id === id)
  items.value.splice(index, 1)
  await useHttp(`${apiUrl}/${id}`, { method: 'DELETE' })
  useGlobalMessage(`Проект <b>${id}</b> удалён`, 'success')
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <template #buttons>
      <v-btn color="primary" :to="{ name: 'projects-editor' }">
        добавить проект
      </v-btn>
    </template>
    <ProjectList :items="items" @delete="deleteSavedProject" />
  </UiIndexPage>
</template>

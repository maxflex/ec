<script setup lang="ts">
import type { ClientResource } from '.'
import type { SwampListResource } from '../Swamp'

const { client } = defineProps <{ client: ClientResource }>()

const filters = useAvailableYearsFilter()
const isSwampEditor = ref(false)

const { items, indexPageData, reloadData, availableYears } = useIndex<SwampListResource>(
  `swamps`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      client_id: client.id,
    },
  },
)

function onBack() {
  reloadData()
  isSwampEditor.value = false
  smoothScroll('main', 'top', 'instant')
}
</script>

<template>
  <ScheduleDraftEditor
    v-if="isSwampEditor && filters.year"
    :client="client"
    :year="filters.year"
    :swamps="items"
    @back="onBack()"
  />
  <UiIndexPage v-else :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <template #buttons>
      <v-btn v-if="filters.year" color="primary" @click="isSwampEditor = true">
        управление группами
      </v-btn>
    </template>
    <SwampList :items="items" />
  </UiIndexPage>
</template>

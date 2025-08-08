<script setup lang="ts">
import type { ClientResource } from '.'
import type { SwampListResource } from '../Swamp'

const { client } = defineProps <{ client: ClientResource }>()

const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears, reloadData } = useIndex<SwampListResource>(
  `swamps`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      client_id: client.id,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        :to="{ name: 'schedule-drafts-editor', query: { year: filters.year ?? currentAcademicYear(), client_id: client.id } }"
      >
        управление группами
      </v-btn>
    </template>
    <SwampList :items="items" @updated="reloadData" />
  </UiIndexPage>
</template>

<script setup lang="ts">
import type { SwampListResource } from '../Swamp'

const { clientId } = defineProps<{ clientId: number }>()

const filters = useAvailableYearsFilter()
const isSwampEditor = ref(false)

const { items, indexPageData, availableYears } = useIndex<SwampListResource>(
  `swamps`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      client_id: clientId,
    },
  },
)
</script>

<template>
  <SwampEditor v-if="isSwampEditor && filters.year" :client-id="clientId" :year="filters.year" :swamps="items" @back="isSwampEditor = false" />
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

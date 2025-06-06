<script setup lang="ts">
import type { ClientListResource } from '~/components/Client'

const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears } = useIndex<ClientListResource>(
  `clients`,
  filters,
  {
    loadAvailableYears: true,
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <ClientList :items="items" />
  </UiIndexPage>
</template>

<script setup lang="ts">
import type { Filters } from '~/components/Client/Filters.vue'

const { items, onFiltersApply, loading } = useIndex<ClientListResource, Filters>(`clients`)
</script>

<template>
  <UiFilters>
    <ClientFilters @apply="onFiltersApply" />
  </UiFilters>
  <UiLoader3 :loading="loading" />
  <ClientList :items="items" />
</template>

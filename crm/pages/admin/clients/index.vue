<script setup lang="ts">
import type { Filters } from '~/components/Client/Filters.vue'

const { items, onFiltersApply, loading } = useIndex<ClientListResource, Filters>(`clients`)
</script>

<template>
  <div class="filters">
    <ClientFilters @apply="onFiltersApply" />
  </div>
  <UiLoader3 :loading="loading" />
  <ClientList :items="items" />
</template>

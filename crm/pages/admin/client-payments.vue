<script setup lang="ts">
import type { Filters } from '~/components/ClientPayment/Filters.vue'

const { items, loading, onFiltersApply } = useIndex<ClientPaymentResource, Filters>(`client-payments`)
</script>

<template>
  <div class="filters">
    <ClientPaymentFilters @apply="onFiltersApply" />
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <ClientPaymentList :items="items" />
  </div>
</template>

<script setup lang="ts">
import type { ClientPaymentDialog } from '#build/components'
import type { Filters } from '~/components/ClientPayment/Filters.vue'

const clientPaymentDialog = ref<InstanceType<typeof ClientPaymentDialog>>()

const { items, loading, onFiltersApply } = useIndex<ClientPaymentResource, Filters>(`client-payments`)
</script>

<template>
  <div class="filters">
    <ClientPaymentFilters @apply="onFiltersApply" />
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <ClientPaymentList :items="items" @open="clientPaymentDialog?.edit" />
  </div>
  <ClientPaymentDialog ref="clientPaymentDialog" />
</template>

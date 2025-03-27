<script setup lang="ts">
import type { ClientPaymentDialog } from '#build/components'

const { clientId } = defineProps<{ clientId: number }>()
const filters = useAvailableYearsFilter()
const clientPaymentDialog = ref<InstanceType<typeof ClientPaymentDialog>>()

const { items, indexPageData, availableYears } = useIndex<ClientPaymentResource>(
  `client-payments`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      client_id: clientId,
    },
  },
)

function onUpdated(p: ClientPaymentResource) {
  const index = items.value.findIndex(e => e.id === p.id)
  if (index !== -1) {
    items.value[index] = p
  }
  else {
    items.value.unshift(p)
  }
  itemUpdated('client-payment', p.id)
}

function onDeleted(p: ClientPaymentResource) {
  const index = items.value.findIndex(e => e.id === p.id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        @click="clientPaymentDialog?.create(clientId)"
      >
        добавить платеж
      </v-btn>
    </template>
    <ClientPaymentList :items="items" @edit="clientPaymentDialog?.edit" />
  </UiIndexPage>
  <ClientPaymentDialog
    ref="clientPaymentDialog"
    @updated="onUpdated"
    @deleted="onDeleted"
  />
</template>

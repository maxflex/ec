<script setup lang="ts">
import type { ClientPaymentDialog } from '#build/components'

const clientPaymentDialog = ref<InstanceType<typeof ClientPaymentDialog>>()
const filters = ref<ClientPaymentFilters>({
  year: currentAcademicYear(),
})

const { items, indexPageData } = useIndex<ClientPaymentResource, ClientPaymentFilters>(
    `client-payments`,
    filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ClientPaymentFilters v-model="filters" />
    </template>
    <ClientPaymentList :items="items" @open="clientPaymentDialog?.edit" />
  </UiIndexPage>
  <ClientPaymentDialog ref="clientPaymentDialog" />
</template>

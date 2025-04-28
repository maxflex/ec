<script setup lang="ts">
/**
 * @DEPRICATED страница не используется
 */
import type { ClientPaymentDialog } from '#build/components'
import type { ClientPaymentFilters } from '~/components/ClientPayment/Filters.vue'

const clientPaymentDialog = ref<InstanceType<typeof ClientPaymentDialog>>()
const filters = ref<ClientPaymentFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<ClientPaymentResource>(
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

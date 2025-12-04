<script setup lang="ts">
import type { ContractPaymentDialog } from '#components'
import type { AlfaPaymentResource } from '~/components/AlfaPayment'

const contractPaymentDialog = ref<InstanceType<typeof ContractPaymentDialog>>()
const { items, indexPageData, reloadData } = useIndex<AlfaPaymentResource>(`alfa-payments`, ref({}))

const filters = ref<{ company?: Company }>({})

function onCreate(item: AlfaPaymentResource) {
  contractPaymentDialog.value?.createFromAlfaPayment(item)
}

const filteredItems = computed(() => {
  if (filters.value.company) {
    return items.value.filter(e => e.contract.company === filters.value.company)
  }

  return items.value
})
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect
        v-model="filters.company"
        label="Компания"
        :items="selectItems(CompanyLabel)"
        density="comfortable"
      />
    </template>
    <UiNoData v-if="filteredItems.length === 0" />
    <AlfaPaymentList v-else :items="filteredItems" @create="onCreate" />
  </UiIndexPage>
  <ContractPaymentDialog ref="contractPaymentDialog" @updated="reloadData" />
</template>

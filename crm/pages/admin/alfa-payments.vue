<script setup lang="ts">
import type { ContractPaymentDialog } from '#components'
import type { AlfaPaymentResource } from '~/components/AlfaPayment'

const contractPaymentDialog = ref<InstanceType<typeof ContractPaymentDialog>>()
const { items, indexPageData, reloadData } = useIndex<AlfaPaymentResource>(`alfa-payments`, ref({}))

function onCreate(item: AlfaPaymentResource) {
  contractPaymentDialog.value?.createFromAlfaPayment(item)
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <AlfaPaymentList :items="items" @create="onCreate" />
  </UiIndexPage>
  <ContractPaymentDialog ref="contractPaymentDialog" @updated="reloadData" />
</template>

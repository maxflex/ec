<script setup lang="ts">
import type { ClientPaymentDialog, ContractDialog } from '#build/components'

const { contracts } = defineProps<{ contracts: ContractResource[] }>()
const clientPaymentDialog = ref<InstanceType<typeof ClientPaymentDialog>>()
const contractDialog = ref<InstanceType<typeof ContractDialog>>()
</script>

<template>
  <div class="contract-list">
    <div
      v-for="contract in contracts"
      :key="contract.id"
    >
      <h3>
        Договор №{{ contract.id }} на {{ formatYear(contract.year) }}
        <v-btn
          color="gray"
          :size="48"
          icon="$plus"
          variant="plain"
          @click="() => contractDialog?.create(contract)"
        />
      </h3>
      <ContractVersionList
        :contract="contract"
        @open="onOpen"
      />
      <h3>
        Платежи
        <v-btn
          color="gray"
          :size="48"
          icon="$plus"
          variant="plain"
          @click="() => clientPaymentDialog?.create(contract)"
        />
      </h3>
      <!-- <ClientPaymentList
        :items="contract.payments"
        @open="(p) => clientPaymentDialog?.open(p)"
      /> -->
    </div>
    <ContractDialog ref="contractDialog" />
    <!-- <ClientPaymentDialog ref="clientPaymentDialog" /> -->
  </div>
</template>

<style lang="scss">
.contract-list {
  & > div {
    h3:not(:first-child) {
      margin-top: 30px;
    }
    &:not(:first-child) {
      margin-top: 50px;
    }
  }
}
</style>

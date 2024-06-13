<script setup lang="ts">
import { mdiWalletBifoldOutline } from '@mdi/js'
import type { ClientPaymentDialog, ContractBalanceDialog, ContractDialog } from '#build/components'

const { items } = defineProps<{ items: ContractResource[] }>()
const clientPaymentDialog = ref<InstanceType<typeof ClientPaymentDialog>>()
const contractDialog = ref<InstanceType<typeof ContractDialog>>()
const contractBalanceDialog = ref<InstanceType<typeof ContractBalanceDialog>>()

function onContractUpdated(c: ContractResource) {
  const index = items.findIndex(e => e.id === c.id)
  if (index === -1) {
    // eslint-disable-next-line
    items.unshift(c)
    smoothScroll('main', 'top')
  }
  else {
    // сносим весь договор
    if (c.versions.length === 0) {
      // eslint-disable-next-line
      items.splice(index, 1)
    }
    else {
      // eslint-disable-next-line
      items.splice(index, 1, c)
    }
  }
}

function onClientPaymentUpdated(cp: ClientPaymentResource) {
  const contractIndex = items.findIndex(c => c.id === cp.entity_id)
  if (contractIndex === -1) {
    return
  }
  const index = items[contractIndex].payments.findIndex(e => e.id === cp.id)
  if (index === -1) {
    // eslint-disable-next-line
    items[contractIndex].payments.unshift(cp)
  }
  else {
    // eslint-disable-next-line
    items[contractIndex].payments.splice(index, 1, cp)
  }
}

function onClientPaymentDestroyed(cp: ClientPaymentResource) {
  const contractIndex = items.findIndex(c => c.id === cp.entity_id)
  if (contractIndex === -1) {
    return
  }
  const index = items[contractIndex].payments.findIndex(e => e.id === cp.id)
  if (index !== -1) {
    // eslint-disable-next-line
    items[contractIndex].payments.splice(index, 1)
  }
}
</script>

<template>
  <div class="contract-list">
    <div
      v-for="contract in items"
      :key="contract.id"
      class="contract-list__item"
    >
      <h3>
        Договор №{{ contract.id }} на {{ formatYear(contract.year) }}
        <div class="contract-list__actions">
          <v-btn
            color="gray"
            icon="$plus"
            variant="plain"
            :size="48"
            @click="() => contractDialog?.addVersion(contract)"
          />
          <v-btn
            color="gray"
            :icon="mdiWalletBifoldOutline"
            variant="plain"
            :size="48"
            @click="() => contractBalanceDialog?.open(contract.id)"
          />
        </div>
      </h3>
      <ContractVersionList
        :items="contract.versions"
        @edit="versionIndex => contractDialog?.editVersion(contract, versionIndex)"
      />
      <h3>
        Платежи
        <div class="contract-list__actions">
          <v-btn
            color="gray"
            :size="48"
            icon="$plus"
            variant="plain"
            @click="() => clientPaymentDialog?.create(contract)"
          />
        </div>
      </h3>
      <ClientPaymentList
        :items="contract.payments"
        @open="(p) => clientPaymentDialog?.open(p)"
      />
    </div>
    <div class="contract-list__add">
      <v-btn
        color="primary"
        @click="contractDialog?.create()"
      >
        добавить новый договор
      </v-btn>
    </div>
  </div>
  <ContractDialog
    ref="contractDialog"
    @updated="onContractUpdated"
  />
  <ClientPaymentDialog
    ref="clientPaymentDialog"
    @updated="onClientPaymentUpdated"
    @destroyed="onClientPaymentDestroyed"
  />
  <ContractBalanceDialog ref="contractBalanceDialog" />
</template>

<style lang="scss">
.contract-list {
  h3 {
    margin-bottom: 20px;
    padding: 0 20px;
    display: flex;
    align-items: center;
  }
  &__actions {
    display: flex;
    align-items: center;
    margin-left: 6px;
    .v-icon {
      font-size: calc(var(--v-icon-size-multiplier) * 1.5rem) !important;
    }
    & > .v-btn:nth-child(2) {
      position: relative;
      left: -10px;
    }
  }
  &__item {
    // отступ между цепями договоров
    margin-bottom: 80px;
    // отступ платежи по договору
    h3:not(:first-child) {
      margin-top: 30px;
    }
    &:first-child {
      padding-top: 20px;
    }
  }
  &__add {
    padding: 20px;
  }
}
</style>

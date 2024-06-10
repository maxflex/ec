<script setup lang="ts">
import type { ClientPaymentDialog, ContractDialog } from '#build/components'

const { items } = defineProps<{ items: ContractResource[] }>()
const clientPaymentDialog = ref<InstanceType<typeof ClientPaymentDialog>>()
const contractDialog = ref<InstanceType<typeof ContractDialog>>()

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
    >
      <h3>
        Договор №{{ contract.id }} на {{ formatYear(contract.year) }}
        <v-btn
          color="gray"
          :size="48"
          icon="$plus"
          variant="plain"
          @click="() => contractDialog?.addVersion(contract)"
        />
      </h3>
      <ContractVersionList
        :items="contract.versions"
        @edit="versionIndex => contractDialog?.editVersion(contract, versionIndex)"
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
</template>

<style lang="scss">
.contract-list {
  h3 {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    .v-btn {
      margin-left: 2px;
    }
    .v-icon {
      font-size: calc(var(--v-icon-size-multiplier) * 1.5rem) !important;
    }
  }
  & > div {
    h3:not(:first-child) {
      margin-top: 30px;
    }
    margin-top: 80px;
    &:first-child {
      margin-top: 0 !important;
      padding-top: 0 !important;
    }
  }
  &__add {
    padding: 0;
  }
}
</style>

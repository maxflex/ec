<script setup lang="ts">
import { mdiWalletBifoldOutline } from '@mdi/js'
import type { ClientPaymentDialog, ContractBalanceDialog, ContractVersionDialog } from '#build/components'

const { items } = defineProps<{ items: ContractResource[] }>()
const clientPaymentDialog = ref<InstanceType<typeof ClientPaymentDialog>>()
const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
const contractBalanceDialog = ref<InstanceType<typeof ContractBalanceDialog>>()
const selected = ref(0)

const selectedContract = computed(() => items[selected.value])

function onContractVersionUpdated(cv: ContractVersionListResource) {
  const contractIndex = items.findIndex(c => c.id === cv.contract.id)
  if (contractIndex === -1) {
    return
  }
  const index = items[contractIndex].versions.findIndex(e => e.id === cv.id)
  if (index !== -1) {
    // eslint-disable-next-line
    items[contractIndex].versions[index] = cv
  }
  else {
    // eslint-disable-next-line
    items[contractIndex].versions.unshift(cv)
  }
  itemUpdated('contract-version', cv.id)
}

function onContractCreated(c: ContractResource) {
  // eslint-disable-next-line
  items.unshift(c)
  itemUpdated('contract-version', c.versions[0].id)
}

function onContractVersionDeleted(cv: ContractVersionResource) {
  const contractIndex = items.findIndex(c => c.id === cv.contract.id)
  if (contractIndex === -1) {
    return
  }
  const index = items[contractIndex].versions.findIndex(e => e.id === cv.id)
  if (index === -1) {
    return
  }
  // await itemDeleted('contract-version', cv.id)
  // eslint-disable-next-line
  items[contractIndex].versions.splice(index, 1)
  // сносим весь договор?
  if (items[contractIndex].versions.length === 0) {
    // eslint-disable-next-line
    items.splice(contractIndex, 1)
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
    items[contractIndex].payments[index] = cp
  }
  itemUpdated('client-payment', cp.id)
}

function onClientPaymentDeleted(cp: ClientPaymentResource) {
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
  <div class="filters">
    <div>
      <v-btn
        v-for="(contract, i) in items"
        :key="contract.id"
        class="tab-btn"
        :class="{ 'tab-btn--active': selected === i }"
        variant="plain"
        :ripple="false"
        @click="selected = i"
      >
        договор №{{ contract.id }}
      </v-btn>
    </div>
    <v-btn
      color="primary"
      @click="contractVersionDialog?.createContract()"
    >
      новый договор
    </v-btn>
  </div>
  <div class="contract-list">
    <div
      class="contract-list__item"
    >
      <h3>
        Договор №{{ selectedContract.id }} на {{ formatYear(selectedContract.year) }}
        <div class="contract-list__actions">
          <v-btn
            color="gray"
            icon="$plus"
            variant="plain"
            :size="48"
            @click="() => contractVersionDialog?.addVersion(selectedContract)"
          />
          <v-btn
            color="gray"
            :icon="mdiWalletBifoldOutline"
            variant="plain"
            :size="48"
            @click="() => contractBalanceDialog?.open(selectedContract.id)"
          />
        </div>
      </h3>
      <ContractVersionList2
        :items="selectedContract.versions"
        @edit="contractVersionDialog?.edit"
      />
      <h3>
        Платежи
        <div class="contract-list__actions">
          <v-btn
            color="gray"
            :size="48"
            icon="$plus"
            variant="plain"
            @click="() => clientPaymentDialog?.create(selectedContract)"
          />
        </div>
      </h3>
      <ClientPaymentList
        :items="selectedContract.payments"
        @open="(p) => clientPaymentDialog?.open(p)"
      />
    </div>
  </div>
  <ContractVersionDialog
    ref="contractVersionDialog"
    @updated="onContractVersionUpdated"
    @deleted="onContractVersionDeleted"
    @created="onContractCreated"
  />
  <ClientPaymentDialog
    ref="clientPaymentDialog"
    @updated="onClientPaymentUpdated"
    @deleted="onClientPaymentDeleted"
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

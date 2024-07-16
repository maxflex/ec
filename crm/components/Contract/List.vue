<script setup lang="ts">
import { mdiWalletBifoldOutline } from '@mdi/js'
import type { ContractBalanceDialog, ContractPaymentDialog, ContractVersionDialog } from '#build/components'

const { items } = defineProps<{ items: ContractResource[] }>()
const contractPaymentDialog = ref<InstanceType<typeof ContractPaymentDialog>>()
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

function onContractPaymentUpdated(cp: ContractPaymentResource) {
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

function onContractPaymentDeleted(cp: ContractPaymentResource) {
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
        <div>
          Договор №{{ contract.id }}
        </div>
        <div>
          на {{ formatYear(contract.year) }}
        </div>
      </v-btn>
    </div>
    <v-menu>
      <template #activator="{ props }">
        <v-btn color="primary" v-bind="props">
          действия
        </v-btn>
      </template>
      <v-list>
        <v-list-item @click="contractVersionDialog?.createContract()">
          новый договор
        </v-list-item>
        <v-list-item @click="() => contractBalanceDialog?.open(selectedContract.id)">
          баланс по договору
        </v-list-item>
        <v-list-item
          @click="() => contractVersionDialog?.addVersion(selectedContract)"
        >
          добавить версию
        </v-list-item>
        <v-list-item @click="() => contractPaymentDialog?.create(selectedContract)">
          добавить платеж
        </v-list-item>
      </v-list>
    </v-menu>
  </div>
  <div class="contract-list">
    <div
      class="contract-list__item pt-0"
    >
      <ContractVersionList2
        :items="selectedContract.versions"
        @edit="contractVersionDialog?.edit"
      />
      <ContractPaymentList
        :items="selectedContract.payments"
        @open="contractPaymentDialog?.edit"
      />
    </div>
  </div>
  <ContractVersionDialog
    ref="contractVersionDialog"
    @updated="onContractVersionUpdated"
    @deleted="onContractVersionDeleted"
    @created="onContractCreated"
  />
  <ContractPaymentDialog
    ref="contractPaymentDialog"
    @updated="onContractPaymentUpdated"
    @deleted="onContractPaymentDeleted"
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
  .client-payments {
    padding-top: 57px;
    position: relative;
    &:before {
      content: '';
      top: 0;
      left: 0;
      width: 100%;
      height: 57px;
      background: #fafafa;
      position: absolute;
      border-bottom: 1px solid #e0e0e0;
    }
  }
}
</style>

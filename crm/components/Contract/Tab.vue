<script setup lang="ts">
import type { ContractPaymentDialog, ContractVersionDialog } from '#build/components'

const { clientId } = defineProps <{ clientId: number }>()
const items = ref<ContractResource[]>([])
const contractPaymentDialog = ref<InstanceType<typeof ContractPaymentDialog>>()
const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
const selected = ref(0)

const selectedContract = computed<ContractResource | null>(
  () => items.value.length ? items.value[selected.value] : null,
)

async function onContractVersionUpdated(mode: ContractEditMode, c: ContractVersionListResource | ContractResource) {
  let updatedId: number
  switch (mode) {
    case 'new-contract':
      const newContract = c as ContractResource
      items.value.unshift(newContract)
      selected.value = 0
      updatedId = newContract.versions[0].id
      break

    case 'new-version':
    case 'edit':
      updatedId = (c as ContractVersionListResource).id
      await loadData()
      break
  }
  itemUpdated('contract-version', updatedId!)
}

function onContractVersionDeleted(cv: ContractVersionResource) {
  const contractIndex = items.value.findIndex(c => c.id === cv.contract.id)
  if (contractIndex === -1) {
    return
  }
  const index = items.value[contractIndex].versions.findIndex(e => e.id === cv.id)
  if (index === -1) {
    return
  }
  // await itemDeleted('contract-version', cv.id)

  items.value[contractIndex].versions.splice(index, 1)
  // сносим весь договор?
  if (items.value[contractIndex].versions.length === 0) {
    items.value.splice(contractIndex, 1)
  }
}

function onContractPaymentUpdated(cp: ContractPaymentResource) {
  const contractIndex = items.value.findIndex(c => c.id === cp.contract_id)
  if (contractIndex === -1) {
    return
  }
  const index = items.value[contractIndex].payments.findIndex(e => e.id === cp.id)
  if (index === -1) {
    items.value[contractIndex].payments.unshift(cp)
  }
  else {
    items.value[contractIndex].payments[index] = cp
  }
  itemUpdated('client-payment', cp.id)
}

function onContractPaymentDeleted(cp: ContractPaymentResource) {
  const contractIndex = items.value.findIndex(c => c.id === cp.contract_id)
  if (contractIndex === -1) {
    return
  }
  const index = items.value[contractIndex].payments.findIndex(e => e.id === cp.id)
  if (index !== -1) {
    items.value[contractIndex].payments.splice(index, 1)
  }
}

async function loadData() {
  console.log('Loading data...')
  const { data } = await useHttp<ApiResponse<ContractResource[]>>(
      `contracts`,
      {
        params: {
          client_id: clientId,
        },
      },
  )
  if (data.value) {
    items.value = data.value.data
  }
}

nextTick(loadData)
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
        <v-list-item @click="contractVersionDialog?.newContract()">
          новый договор
        </v-list-item>
        <v-list-item
          v-if="selectedContract"
          @click="() => contractVersionDialog?.newVersion(selectedContract!)"
        >
          добавить версию
        </v-list-item>
        <v-list-item
          v-if="selectedContract"
          @click="() => contractPaymentDialog?.create(selectedContract!)"
        >
          добавить платеж
        </v-list-item>
      </v-list>
    </v-menu>
  </div>
  <div v-if="selectedContract" class="contract-list">
    <div
      class="contract-list__item"
    >
      <ContractVersionList2
        :items="selectedContract.versions"
        @edit="contractVersionDialog?.edit"
      />
      <ContractPaymentList
        :items="selectedContract.payments"
        @open="contractPaymentDialog?.edit"
      />
      <BalanceList :id="selectedContract.id" entity="contract" />
    </div>
  </div>
  <ContractVersionDialog
    ref="contractVersionDialog"
    @updated="onContractVersionUpdated"
    @deleted="onContractVersionDeleted"
  />
  <ContractPaymentDialog
    ref="contractPaymentDialog"
    @updated="onContractPaymentUpdated"
    @deleted="onContractPaymentDeleted"
  />
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
    display: flex;
    flex-direction: column;
    gap: 57px;
    & > * {
      position: relative;
      &:before {
        content: '';
        bottom: -57px;
        left: 0;
        width: 100%;
        height: 57px;
        background: #fafafa;
        position: absolute;
        border-bottom: 1px solid #e0e0e0;
      }
    }
  }
  &__add {
    padding: 20px;
  }
}
</style>

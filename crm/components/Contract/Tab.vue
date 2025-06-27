<script setup lang="ts">
import type { ContractPaymentDialog, ContractVersionDialog } from '#build/components'
import type { ContractPaymentResource } from '~/components/ContractPayment'

const { clientId } = defineProps <{ clientId: number }>()
const items = ref<ContractResource[]>([])
const contractPaymentDialog = ref<InstanceType<typeof ContractPaymentDialog>>()
const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
const selected = ref(0)
const loading = ref(true)
const showBalance = ref(false)

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
  itemUpdated('contract-payment', cp.id)
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
  loading.value = true
  const { data } = await useHttp<ApiResponse<ContractResource>>(
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
  loading.value = false
}

function selectTab(i: number) {
  selected.value = i
  showBalance.value = false
}

const noData = computed(() => !loading.value && items.value.length === 0)

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }" class="contract-tab separate-content">
    <template #filters>
      <v-btn
        v-for="(contract, i) in items"
        :key="contract.id"
        class="tab-btn"
        :class="{ 'tab-btn--active': selected === i }"
        variant="plain"
        :ripple="false"
        @click="selectTab(i)"
      >
        <div>
          №{{ contract.id }} {{ CompanyLabel[contract.company] }}
        </div>
        <div>
          на {{ YearLabel[contract.year] }}
        </div>
      </v-btn>
    </template>
    <template #buttons>
      <v-btn v-if="showBalance" color="primary" :width="132" @click="showBalance = false">
        <template #prepend>
          <v-icon icon="$back" />
        </template>
        назад
      </v-btn>
      <v-menu v-else>
        <template #activator="{ props }">
          <v-btn color="primary" v-bind="props">
            действия
          </v-btn>
        </template>
        <v-list>
          <v-list-item @click="contractVersionDialog?.newContract()">
            новый договор
          </v-list-item>
          <template v-if="selectedContract">
            <v-list-item @click="() => contractVersionDialog?.newVersion(selectedContract!)">
              добавить версию
            </v-list-item>
            <v-list-item @click="() => contractPaymentDialog?.create(selectedContract!)">
              добавить платеж
            </v-list-item>
            <v-list-item @click="showBalance = true">
              показать баланс
            </v-list-item>
          </template>
        </v-list>
      </v-menu>
    </template>
    <template v-if="selectedContract">
      <Balance v-if="showBalance" :contract-id="selectedContract.id" />
      <div v-else>
        <ContractVersionList
          :items="selectedContract.versions"
          @edit="contractVersionDialog?.edit"
        />
        <ContractPaymentList
          v-if="selectedContract.payments.length"
          :items="selectedContract.payments"
          @edit="contractPaymentDialog?.edit"
        />
        <div v-if="selectedContract.balances" class="table contract-tab-balances table--padding">
          <div>
            <div style="width: 630px" class="font-weight-bold">
              итого
            </div>
            <div style="width: 220px">
              <div>
                доплата по договору
              </div>
              <div>
                баланс по услугам
              </div>
            </div>
            <div style="width: 120px" class="font-weight-bold">
              <div>
                {{ formatPrice(selectedContract.balances.to_pay, true) }} руб.
              </div>
              <div>
                {{ formatPrice(selectedContract.balances.remainder, true) }} руб.
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </UiIndexPage>

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
.contract-tab {
  .filters__inputs {
    flex-direction: row-reverse;
  }

  .index-page__content {
    background: white !important;
  }
}
.contract-tab-balances {
  & > div {
    align-items: flex-start !important;
    border: none !important;
  }
}
</style>

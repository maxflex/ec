<script setup lang="ts">
import type { ContractPaymentDialog, ContractVersionDialog } from '#build/components'
import type { SavedScheduleDraftResource } from '../ScheduleDraft'
import type { ContractPaymentResource } from '~/components/ContractPayment'

const { clientId } = defineProps <{ clientId: number }>()
const items = ref<ContractResource[]>([])
const contractPaymentDialog = ref<InstanceType<typeof ContractPaymentDialog>>()
const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
const selected = ref(0)
const scheduleDrafts = ref<SavedScheduleDraftResource[]>([])
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
  await loadScheduleDrafts()
}

async function loadScheduleDrafts() {
  const { data } = await useHttp<ApiResponse<SavedScheduleDraftResource>>(
    `schedule-drafts`,
    {
      params: {
        client_id: clientId,
      },
    },
  )
  scheduleDrafts.value = data.value!.data
}

async function createContract(d: SavedScheduleDraftResource) {
  const { data } = await useHttp<{
    contractVersion: ContractVersionResource
    scheduleDraft: SavedScheduleDraftResource
  }>(
    `schedule-drafts/create-contract`,
    {
      method: 'POST',
      body: {
        schedule_draft_id: d.id,
        contract_id: selectedContract.value?.id,
      },
    },
  )
  contractVersionDialog.value?.createFromDraft(data.value!.contractVersion, d)
}

function selectTab(i: number) {
  selected.value = i
  showBalance.value = false
}

const noData = computed(() => !loading.value && items.value.length === 0)

const scheduleDraftsByContract = computed(() => {
  const result = {}
  for (const item of items.value) {
    const activeVersion = item.versions.find(e => e.is_active)!
    result[item.id] = scheduleDrafts.value.filter(e => e.contract_id === item.id && e.created_at > activeVersion.created_at)
  }
  result[-1] = scheduleDrafts.value.filter(e => !e.contract_id)
  return result
})

// changes by contractId
const changes = computed<Record<number, number>>(() => {
  const result = {}
  for (const item of items.value) {
    const drafts = (item.id in scheduleDraftsByContract.value) ? scheduleDraftsByContract.value[item.id] : []
    result[item.id] = drafts.length ? drafts[drafts.length - 1].changes : 0
  }
  const drafts = (-1 in scheduleDraftsByContract.value) ? scheduleDraftsByContract.value[-1] : []
  result[-1] = drafts.length ? drafts[drafts.length - 1].changes : 0
  return result
})

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }" class="contract-tab separate-content">
    <template #filters>
      <v-btn
        class="tab-btn"
        variant="plain"
        :ripple="false"
        :class="{ 'tab-btn--active': selected === -1 }"
        @click="selectTab(-1)"
      >
        <div>
          <div>
            новый
          </div>
          <div>
            договор
          </div>
        </div>
        <v-badge
          v-if="-1 in changes"
          color="orange-lighten-3 ml-2 pa-0"
          inline
          :content="changes[-1]"
        ></v-badge>
      </v-btn>
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
          <div>
            №{{ contract.id }} {{ CompanyLabel[contract.company] }}
          </div>
          <div>
            на {{ YearLabel[contract.year] }}
          </div>
        </div>
        <v-badge
          v-if="changes[contract.id]"
          color="orange-lighten-3 ml-2 pa-0"
          inline
          :content="changes[contract.id]"
        ></v-badge>
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
        <v-list v-if="selectedContract">
          <v-list-item @click="() => contractVersionDialog?.newVersion(selectedContract!)">
            добавить версию
          </v-list-item>
          <v-list-item
            v-for="d in scheduleDraftsByContract[selectedContract.id]"
            :key="d.id"
            @click="createContract(d)"
          >
            добавить версию по проекту №{{ d.id }}
            <v-badge
              color="orange-lighten-3 ml-2 pa-0"
              inline
              :content="d.changes"
            ></v-badge>
          </v-list-item>
          <v-list-item @click="() => contractPaymentDialog?.create(selectedContract!)">
            добавить платеж
          </v-list-item>
          <v-list-item @click="showBalance = true">
            показать баланс
          </v-list-item>
          <v-list-item
            v-for="d in scheduleDraftsByContract[selectedContract.id]"
            :key="d.id"
            target="_blank"
            :to="{
              name: 'schedule-drafts-editor',
              query: {
                id: d.id,
              },
            }"
          >
            посмотреть проект №{{ d.id }}
            <v-badge
              color="orange-lighten-3 ml-2 pa-0"
              inline
              :content="d.changes"
            ></v-badge>
          </v-list-item>
          <!-- <v-list-item
              v-if="selectedContract.id in changes"
              target="_blank"
              :to="{
                name: 'schedule-drafts-editor',
                query: {
                  id: changes[selectedContract.id].schedule_draft_id,
                  contract_id: selectedContract.id,
                },
              }"
            >
              посмотреть проект
              <v-badge
                color="orange-lighten-3 ml-2 pa-0"
                inline
                :content="changes[selectedContract.id].changes_count"
              ></v-badge>
            </v-list-item> -->
        </v-list>
        <v-list v-else>
          <v-list-item @click="contractVersionDialog?.newContract()">
            новый договор
          </v-list-item>
          <v-list-item v-for="d in scheduleDraftsByContract[-1]" :key="d.id" @click="contractVersionDialog?.newContract()">
            новый договор по проекту №{{ d.id }}
            <v-badge
              v-if="changes[-1]"
              color="orange-lighten-3 ml-2 pa-0"
              inline
              :content="changes[-1]"
            ></v-badge>
          </v-list-item>
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
            <div style="width: 590px" class="font-weight-bold">
              итого
            </div>
            <div style="width: 200px">
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

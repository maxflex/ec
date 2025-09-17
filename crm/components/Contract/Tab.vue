<script setup lang="ts">
import type { ContractEditSourceDialog, ContractPaymentDialog, ContractVersionDialog } from '#build/components'
import type { ContractResource, ContractVersionListResource, ContractVersionResource } from '../ContractVersion'
import type { ProjectResource } from '../Project'
import type { ContractPaymentResource } from '~/components/ContractPayment'

type ProjectsByContract = Record<number, {
  projects: ProjectResource[]
  changes: number
}>

const { clientId } = defineProps <{ clientId: number }>()
const items = ref<ContractResource[]>([])
const contractPaymentDialog = ref<InstanceType<typeof ContractPaymentDialog>>()
const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
const contractEditSourceDialog = ref<InstanceType<typeof ContractEditSourceDialog>>()
const selected = ref(-1) // -1 это "новый договор"
const projects = ref<ProjectResource[]>([])
const loading = ref(true)
const showBalance = ref(false)
const route = useRoute()

const selectedContract = computed<ContractResource | null>(
  () => items.value.length ? items.value[selected.value] : null,
)

async function onContractVersionUpdated(mode: ContractEditMode, c: ContractVersionListResource | ContractResource) {
  let updatedId: number
  switch (mode) {
    case 'new-contract':
      const newContract = c as ContractResource
      items.value.push(newContract)
      selected.value = items.value.length - 1
      updatedId = newContract.versions[0].id
      break

    case 'new-version':
    case 'edit':
      updatedId = (c as ContractVersionListResource).id
      await loadData()
      break
  }
  itemUpdated('contract-version', updatedId!)
  loadProjects()
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
  items.value = data.value!.data
  if (items.value.length > 0) {
    if (route.query.contract_id) {
      selected.value = items.value.findIndex(e => e.id === Number.parseInt(route.query.contract_id as unknown as string))
    }
    else {
      selected.value = items.value.length - 1
    }
  }
  await loadProjects()
  loading.value = false
}

async function loadProjects() {
  const { data } = await useHttp<ApiResponse<ProjectResource>>(
    `projects`,
    {
      params: {
        client_id: clientId,
      },
    },
  )
  projects.value = data.value!.data
}

function selectTab(i: number) {
  selected.value = i
  showBalance.value = false
}

function showBalanceGo(i: number) {
  selected.value = i
  showBalance.value = true
}

const noData = computed(() => !loading.value && items.value.length === 0)

const projectsByContract = computed<ProjectsByContract>(() => {
  const result: ProjectsByContract = {}
  const activeProjects = projects.value.filter(e => !e.is_archived)
  const contractIds = [
    ...items.value.map(e => e.id),
    null, // новый договор
  ]

  for (const contractId of contractIds) {
    const contractProjects = activeProjects.filter(d => d.contract_id === contractId)

    result[contractId || -1] = {
      projects: contractProjects,
      changes: contractProjects[0]?.changes ?? 0,
    }
  }

  return result
})

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }" class="contract-tab separate-content">
    <template v-if="!loading" #filters>
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
          v-if="projectsByContract[contract.id]?.changes"
          color="orange-lighten-3"
          inline
          :content="projectsByContract[contract.id].changes"
        ></v-badge>
        <v-menu>
          <template #activator="{ props }">
            <v-icon icon="$next" v-bind="props" />
          </template>
          <v-list>
            <v-list-item @click="selected = i; contractVersionDialog?.newVersion(contract)">
              добавить версию
            </v-list-item>
            <v-list-item @click="selected = i; contractPaymentDialog?.create(selectedContract!)">
              добавить платеж
            </v-list-item>
            <v-list-item @click="showBalanceGo(i)">
              показать баланс
            </v-list-item>
            <v-list-item @click="contractEditSourceDialog?.open(contract)">
              редактировать источник
            </v-list-item>
            <v-list-item
              v-for="d in projectsByContract[contract.id].projects"
              :key="d.id"
              @click="selected = i; contractVersionDialog?.fromProject({ savedProject: d })"
            >
              <div>
                добавить версию на основе проекта {{ d.id }}
                <v-badge
                  color="orange-lighten-3"
                  inline
                  :content="d.changes"
                ></v-badge>
              </div>
              <div class="text-caption text-gray">
                Создал {{ formatName(d.user) }} {{ formatDateTime(d.created_at) }}
              </div>
            </v-list-item>
            <v-list-item
              v-for="d in projectsByContract[contract.id].projects"
              :key="d.id"
              target="_blank"
              :to="{
                name: 'projects-editor',
                query: {
                  id: d.id,
                },
              }"
            >
              <div>
                посмотреть проект №{{ d.id }}
                <v-badge
                  v-if="d.changes"
                  color="orange-lighten-3"
                  inline
                  :content="d.changes"
                ></v-badge>
              </div>
              <div class="text-caption text-gray">
                Создал {{ formatName(d.user) }} {{ formatDateTime(d.created_at) }}
              </div>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-btn>

      <!-- новый договор -->

      <v-menu>
        <template #activator="{ props }">
          <v-btn
            v-bind="props"
            class="tab-btn"
            variant="plain"
            :ripple="false"
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
              v-if="projectsByContract[-1]?.changes"
              color="orange-lighten-3"
              inline
              :content="projectsByContract[-1].changes"
            ></v-badge>
            <v-icon icon="$next" />
          </v-btn>
        </template>
        <v-list>
          <v-list-item @click="selected = -1; contractVersionDialog?.newContract(clientId)">
            создать новый договор
          </v-list-item>
          <v-list-item
            v-for="d in projectsByContract[-1].projects"
            :key="d.id"
            @click="selected = -1; contractVersionDialog?.fromProject({ savedProject: d })"
          >
            <div>
              создать новый договор на основе проекта {{ d.id }}
              <v-badge
                v-if="projectsByContract[-1]?.changes"
                color="orange-lighten-3"
                inline
                :content="projectsByContract[-1].changes"
              ></v-badge>
            </div>
            <div class="text-caption text-gray">
              Создал {{ formatName(d.user) }} {{ formatDateTime(d.created_at) }}
            </div>
          </v-list-item>
          <v-list-item
            v-for="d in projectsByContract[-1].projects"
            :key="d.id"
            target="_blank"
            :to="{
              name: 'projects-editor',
              query: {
                id: d.id,
              },
            }"
          >
            <div>
              посмотреть проект №{{ d.id }}
              <v-badge
                v-if="d.changes"
                color="orange-lighten-3 ml-2 pa-0"
                inline
                :content="d.changes"
              ></v-badge>
            </div>
            <div class="text-caption text-gray">
              Создал {{ formatName(d.user) }} {{ formatDateTime(d.created_at) }}
            </div>
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
    <template #buttons>
      <v-btn v-if="showBalance" color="primary" :width="132" @click="showBalance = false">
        <template #prepend>
          <v-icon icon="$back" />
        </template>
        назад
      </v-btn>
    </template>
    <template v-if="selectedContract">
      <Balance v-if="showBalance" :key="selected" :contract-id="selectedContract.id" />
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
  <ContractEditSourceDialog ref="contractEditSourceDialog" />
</template>

<style lang="scss">
.contract-tab {
  .index-page__content {
    background: white !important;
  }

  .filters__inputs {
    button {
      .v-icon {
        $size: 20px;
        height: $size;
        width: $size;
        font-size: 14px;
        transform: rotate(90deg);
        transition: transform ease-in-out 0.2s;
        &[aria-expanded='true'] {
          transform: rotate(-90deg);
        }
      }

      &[aria-expanded='true'] {
        .v-icon {
          transform: rotate(-90deg);
        }
      }
    }
  }

  .v-btn__content {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .v-badge__wrapper {
    float: left;
  }

  .balance__filters {
    display: none !important;
  }

  .balance {
    border-top: none !important;
  }
}
.contract-tab-balances {
  & > div {
    align-items: flex-start !important;
    border: none !important;
  }
}
</style>

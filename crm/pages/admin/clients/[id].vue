<script setup lang="ts">
import type {
  ClientDialog,
  ClientPaymentDialog,
  ContractDialog,
  GroupSelectorDialog,
  TestSelectorDialog,
} from "#build/components"
import { nextTick } from "vue"
import type {
  Client,
  Contract,
  ContractVersion,
  Group,
  Tests,
} from "~/utils/models"
import { mdiEmailOutline, mdiHistory, mdiPencil } from "@mdi/js"
import { ENTITY_TYPE } from "~/utils/sment"

const route = useRoute()
const client = ref<Client>()
const clientPaymentDialog = ref<null | InstanceType<
  typeof ClientPaymentDialog
>>()
const contractDialog = ref<null | InstanceType<typeof ContractDialog>>()
const clientDialog = ref<null | InstanceType<typeof ClientDialog>>()
const testSelectorDialog = ref<null | InstanceType<typeof TestSelectorDialog>>()
const groupSelectorDialog = ref<null | InstanceType<
  typeof GroupSelectorDialog
>>()

async function loadData() {
  const { data } = await useHttp(`clients/${route.params.id}`)
  client.value = data.value as Client
}

function onOpen(c: Contract, v: ContractVersion) {
  console.log("open", c, v)
  contractDialog.value?.open(c, v)
}

async function onGroupSelected(g: Group) {
  await useHttp(`groups/add-client`, {
    method: "post",
    params: {
      group_id: g.id,
      client_id: client.value?.id,
    },
  })
  loadData()
}

function onTestsSaved(tests: Tests) {
  if (!client.value) {
    return
  }
  client.value.tests = [...tests]
  useHttp(`tests/add-client/${client.value.id}`, {
    method: "post",
    body: { ids: tests.map((t) => t.id) },
  })
}

type ClientTab = "contracts" | "groups" | "tests"
const selectedTab = ref<ClientTab>("contracts")
const tabs: Record<ClientTab, string> = {
  contracts: "договоры",
  groups: "группы",
  tests: "тесты",
}

nextTick(loadData)
</script>

<template>
  <div class="client" v-if="client">
    <div class="client__panel">
      <div class="client__actions">
        <v-btn
          :icon="mdiPencil"
          :size="48"
          variant="plain"
          @click="clientDialog?.open(client)"
        />
        <PreviewModeBtn
          :user="{
            id: client.id,
            entity_type: ENTITY_TYPE.client,
          }"
        />
      </div>
      <div>
        <div>клиент</div>
        <div class="text-truncate">
          {{ formatFullName(client) }}
        </div>
      </div>
      <div>
        <div>контакты</div>
        <div v-if="client.phones" class="client__phones">
          <div v-for="p in client.phones" :key="p.id">
            <div class="client__phones-number">
              <a :href="`tel:${p.number}`">
                {{ formatPhone(p.number as string) }}
              </a>
            </div>
            <div class="client__phones-actions">
              <v-icon :icon="mdiEmailOutline" />
              <v-icon :icon="mdiHistory" />
            </div>
          </div>
        </div>
        <div v-else>не установлено</div>
      </div>
      <div>
        <div>филиалы</div>
        <UiBranches :branches="client.branches" />
      </div>
      <div>
        <div>куратор</div>
        <div v-if="client.head_teacher">
          <router-link
            :to="{
              name: 'teachers-id',
              params: { id: client.head_teacher_id },
            }"
          >
            {{ formatName(client.head_teacher) }}
          </router-link>
        </div>
        <div v-else>не установлено</div>
      </div>
    </div>
    <div class="client__content">
      <div class="client__tabs">
        <div
          class="client__tabs-item"
          :class="{ 'client__tabs-item--active': selectedTab === key }"
          @click="selectedTab = key"
          v-for="(label, key) in tabs"
          :key="key"
        >
          {{ label }}
        </div>
      </div>
      <div class="client-contracts" v-if="selectedTab === 'contracts'">
        <div v-for="contract in client.contracts" :key="contract.id">
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
          <ContractList :contract="contract" @open="onOpen" />
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
        <ContractDialog ref="contractDialog" />
        <ClientPaymentDialog ref="clientPaymentDialog" />
        <!-- <div class="mt-6">
          <v-btn color="primary">Добавить договор</v-btn>
        </div> -->
      </div>
      <div
        v-else-if="selectedTab === 'groups'"
        style="top: -20px; position: relative"
      >
        <div class="table table--padding table--hover table--actions-on-hover">
          <GroupItem
            v-for="group in client.groups"
            :group="group"
            :key="group.id"
          />
          <GroupSwamp
            v-for="swamp in client.swamps"
            :key="swamp.id"
            :swamp="swamp"
            @attach="(s) => groupSelectorDialog?.open(s.program)"
          />
        </div>
        <GroupSelectorDialog
          ref="groupSelectorDialog"
          @select="onGroupSelected"
        />
      </div>
      <div v-else style="top: -20px; position: relative">
        <ClientTestList :tests="client.tests" />
        <TestSelectorDialog ref="testSelectorDialog" @saved="onTestsSaved" />
        <div class="mt-6">
          <v-btn color="primary" @click="() => testSelectorDialog?.open()">
            добавить тест
          </v-btn>
        </div>
      </div>
    </div>
    <ClientDialog ref="clientDialog" />
    <!-- <div>
      <h3>
        Ученик
        <PreviewModeBtn
          :user="{
            id: client.id,
            entity_type: ENTITY_TYPE.client,
          }"
        />
      </h3>
      <div class="inputs">
        <v-text-field v-model="client.last_name" label="Фамилия" />
        <v-text-field v-model="client.first_name" label="Имя" />
        <v-text-field v-model="client.middle_name" label="Отчество" />
      </div>
    </div> -->
  </div>
</template>

<style lang="scss">
.client {
  // padding: 20px;
  display: flex;
  height: 100vh;
  &__phones {
    margin-top: 2px;
    & > div {
      display: flex;
      flex-wrap: nowrap;
      align-items: center;
      &:hover {
        .client__phones-actions {
          opacity: 1;
        }
      }
    }
    &-number {
      min-width: 160px;
    }
    &-actions {
      display: flex;
      gap: 8px;
      flex: 1;
      opacity: 0;
      transition: opacity cubic-bezier(0.4, 0, 0.2, 1) 0.2s;
      .v-icon {
        top: -2px;
        font-size: 18px;
        color: rgb(var(--v-theme-gray));
      }
    }
  }
  &__content {
    flex: 1;
    height: 100vh;
    overflow-y: scroll;
    & > div {
      &:last-child {
        padding: 20px;
      }
    }
  }
  &__panel {
    border-right: 1px solid #e0e0e0;
    width: 256px;
    & > div:not(.client__actions) {
      padding: 0 16px 20px;
      & > div {
        &:first-child {
          color: rgb(var(--v-theme-gray));
        }
      }
    }
  }
  &__actions {
    padding: 6px 6px 20px;
  }
  &__tabs {
    display: flex;
    border-bottom: 1px solid #e0e0e0;
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
    // box-shadow: 0 0 10px 20px rgba(white, 0.5);
    &-item {
      padding: 16px 30px;
      cursor: pointer;
      transition: all cubic-bezier(0.4, 0, 0.2, 1) 0.2s;
      &:hover {
        background: #f6f6f6;
      }
      &--active {
        background: #e4e4e4 !important;
        // pointer-events: none;
      }
    }
  }
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
  // & > div {
  //   &:not(:first-child) {
  //     margin-top: 60px;
  //   }
  // }
  &-contracts {
    & > div {
      h3:not(:first-child) {
        margin-top: 30px;
      }
      &:not(:first-child) {
        margin-top: 50px;
      }
    }
  }
  .table {
    left: -20px;
    position: relative;
    width: calc(100% + 40px);
  }
}
</style>

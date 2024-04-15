<script setup lang="ts">
import type {
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
import { ENTITY_TYPE } from "~/utils/sment"

const route = useRoute()
const client = ref<Client>()
const clientPaymentDialog = ref<null | InstanceType<
  typeof ClientPaymentDialog
>>()
const contractDialog = ref<null | InstanceType<typeof ContractDialog>>()
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

nextTick(loadData)
</script>

<template>
  <!-- <h3 class="page-title">Клиент {{ route.params.id }}</h3> -->
  <div class="client" v-if="client">
    <div>
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
    </div>
    <div>
      <h3>Представитель</h3>
      <div class="inputs">
        <v-text-field v-model="client.parent_last_name" label="Фамилия" />
        <v-text-field v-model="client.parent_first_name" label="Имя" />
        <v-text-field v-model="client.parent_middle_name" label="Отчество" />
      </div>
    </div>

    <div class="client-contracts">
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
      <!-- <div class="mt-8">
        <v-btn color="secondary">Добавить договор</v-btn>
      </div> -->
      <ContractDialog ref="contractDialog" />
      <ClientPaymentDialog ref="clientPaymentDialog" />
    </div>

    <div>
      <h3>Группы</h3>
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

    <div>
      <h3>
        Тесты
        <v-btn
          color="gray"
          :size="48"
          icon="$plus"
          variant="plain"
          @click="() => testSelectorDialog?.open()"
        />
      </h3>
      <TestClientList :tests="client.tests" />
      <TestSelectorDialog ref="testSelectorDialog" @saved="onTestsSaved" />
    </div>
  </div>
</template>

<style lang="scss">
.client {
  padding: 20px;
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
    &:not(:first-child) {
      margin-top: 60px;
    }
  }
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

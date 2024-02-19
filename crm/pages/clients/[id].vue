<script setup lang="ts">
import type { ContractDialog } from "#build/components"
import type { Client, Contract, ContractVersion } from "~/utils/models"

const route = useRoute()
const client = ref<Client>()
const contractDialog = ref<null | InstanceType<typeof ContractDialog>>()

async function loadData() {
  const { data } = await useHttp(`clients/${route.params.id}`)
  client.value = data.value as Client
}

function onOpen(c: Contract, v: ContractVersion) {
  console.log("open", c, v)
  contractDialog.value?.open(c, v)
}

onMounted(async () => {
  await loadData()
})
</script>

<template>
  <!-- <h3 class="page-title">Клиент {{ route.params.id }}</h3> -->
  <div class="client" v-if="client">
    <div>
      <h3>Ученик</h3>
      <v-text-field v-model="client.last_name" label="Фамилия" />
      <v-text-field v-model="client.first_name" label="Имя" />
      <v-text-field v-model="client.middle_name" label="Отчество" />
    </div>
    <div>
      <h3>Представитель</h3>
      <v-text-field v-model="client.parent_last_name" label="Фамилия" />
      <v-text-field v-model="client.parent_first_name" label="Имя" />
      <v-text-field v-model="client.parent_middle_name" label="Отчество" />
    </div>
    <div
      class="client-contracts"
      v-for="contract in client.contracts"
      :key="contract.id"
    >
      <h3>Договор №{{ contract.id }} на {{ formatYear(contract.year) }}</h3>
      <ContractVersions :contract="contract" @open="onOpen" />
    </div>
    <ContractDialog ref="contractDialog" />
    <div>
      <v-btn color="secondary">Добавить договор</v-btn>
    </div>
  </div>
</template>

<style lang="scss">
.client {
  padding: 20px;
  h3 {
    margin-bottom: 20px;
  }
  & > div {
    &:not(:first-child) {
      margin-top: 30px;
    }
  }
  &-contracts {
    margin-top: 50px !important;
  }
}
</style>

<script setup lang="ts">
import type { ContractDialog } from "#build/components"
import type { Client, ContractVersion } from "~/utils/models"

const route = useRoute()
const client = ref<Client>()
const contractDialog = ref<null | InstanceType<typeof ContractDialog>>()

async function loadData() {
  const { data } = await useHttp(`clients/${route.params.id}`)
  client.value = data.value as Client
}

function onOpen(version: ContractVersion) {
  console.log("open", version)
  contractDialog.value?.open(version)
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
      <h3>Договор №{{ contract.id }} на <UiYear :year="contract.year" /></h3>
      <ContractVersions :versions="contract.versions" @open="onOpen" />
    </div>
    <ContractDialog ref="contractDialog" />
    <div>
      <v-btn color="secondary">Сохранить</v-btn>
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
    margin-top: 100px;
    table {
      table-layout: fixed;
      border-collapse: collapse;
      border-spacing: 0;
      // width: 100%;
      left: -20px;
      position: relative;
      width: calc(100% + 40px);
      tr {
        td {
          border-bottom: thin solid
            rgba(var(--v-border-color), var(--v-border-opacity));
          padding: 16px 16px;
          &:first-child {
            padding-left: 20px;
          }
          &:last-child {
            padding-right: 20px;
            position: relative;
            button {
              position: absolute;
              right: 20px;
              top: 4px;
            }
          }
        }
      }
    }
    &__subjects {
      display: flex;
      gap: 10px;
      max-width: 322px;
      & > div {
        display: flex;
        gap: 4px;
      }
      // overflow: hidden;
      // white-space: nowrap;
      // text-overflow: ellipsis;
    }
  }
}
</style>

<script setup lang="ts">
import type { Client } from "~/utils/models"
import { SubjectShort } from "~/utils/sment"

const route = useRoute()
const client = ref<Client>()

async function loadData() {
  const { data } = await useHttp(`clients/${route.params.id}`)
  client.value = data.value as Client
}

onMounted(async () => {
  await loadData()
})
</script>

<template>
  <!-- <h3 class="page-title">Клиент {{ route.params.id }}</h3> -->
  <div class="clients-id" v-if="client">
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
    <div v-for="contract in client.contracts" :key="contract.id">
      <h3>Договор №{{ contract.id }} на <UiYear :year="contract.year" /></h3>
      <table class="contracts-table">
        <tr v-for="version in contract.versions">
          <td width="150">версия {{ version.version }}</td>
          <td width="220">от {{ formatDate(version.date) }}</td>
          <td width="220">{{ version.sum }} руб.</td>
          <td width="220">
            <span v-if="version.payments.length === 0" class="text-grey">
              платежей нет
            </span>
            <template v-else> {{ version.payments.length }} платежей </template>
          </td>
          <td>
            <div class="contracts-table__subjects text-truncate">
              <div v-for="subject in version.subjects" :key="subject.id">
                <span :class="{ 'text-error': subject.is_closed }">
                  {{ SubjectShort[subject.subject] }}
                </span>
                <span class="text-grey">
                  {{ subject.lessons }}
                </span>
              </div>
            </div>
          </td>
          <td width="50" class="text-right">
            <v-btn icon :size="48">
              <v-icon> mdi-dots-horizontal </v-icon>
            </v-btn>
          </td>
        </tr>
      </table>
    </div>
    <div>
      <v-btn color="secondary">Сохранить</v-btn>
    </div>
  </div>
</template>

<style lang="scss">
.clients-id {
  padding: 20px;
  h3 {
    margin-bottom: 20px;
  }
  & > div {
    &:not(:first-child) {
      margin-top: 30px;
    }
  }
  .contracts-table {
    table-layout: fixed;
    border-collapse: collapse;
    border-spacing: 0;
    // width: 100%;
    left: -20px;
    position: relative;
    width: calc(100% + 40px);
    margin-bottom: 50px;
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

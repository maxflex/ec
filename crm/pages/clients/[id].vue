<script setup lang="ts">
import type { Client } from "~/utils/models"

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
}
</style>

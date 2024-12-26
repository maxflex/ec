<script setup lang="ts">
import { mdiCheckBold } from '@mdi/js'

interface NalogItem {
  id: number
  is_marked: boolean
  parent: PersonResource
  seq: number
  date: string
  sum1: number
  sum2: number
  paid: number
}

const items = ref<NalogItem[]>([])
const loading = ref(true)

// Временная страница
async function loadData() {
  const { data } = await useHttp<NalogItem[]>(`nalog-contracts`)
  items.value = data.value
  loading.value = false
}

nextTick(loadData)
</script>

<template>
  <UiLoader v-if="loading" />
  <v-table v-else hover fixed-header style="height: 100%">
    <thead>
      <tr>
        <th>
          представитель
        </th>
        <th>
          договор
        </th>
        <th>
          дата
        </th>
        <th>
          до 31 декабря
        </th>
        <th>
          до 31 февраля
        </th>
        <th>
          оплачено
        </th>
        <th>
          метка
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in items" :key="item.id">
        <td width="260">
          <UiPerson :item="item.parent" />
        </td>
        <td width="180">
          №{{ item.id }}-{{ item.seq }}
        </td>
        <td width="200">
          от {{ formatDate(item.date) }}
        </td>
        <td width="200">
          {{ formatPrice(item.sum1, true) }} руб.
        </td>
        <td width="200">
          {{ formatPrice(item.sum2, true) }} руб.
        </td>
        <td width="200">
          {{ formatPrice(item.paid, true) }} руб.
        </td>
        <td>
          <v-icon v-if="item.is_marked" :icon="mdiCheckBold" color="success" />
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

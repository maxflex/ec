<script setup lang="ts">
interface NalogItem {
  id: number
  parent: PersonResource
  seq: number
  date: string
  sum1: number
  sum2: number
}

const items = ref<NalogItem[]>([])
const loading = ref(true)

// Временная страница
async function loadData() {
  const { data } = await useHttp<NalogNumber[]>(`nalog-contracts`)
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
          company
        </th>
        <th>
          до 31 декабря
        </th>
        <th>
          до 31 февраля
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in items" :key="item.id">
        <td width="280">
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
        <td>{{ formatPrice(item.sum2, true) }} руб.</td>
      </tr>
    </tbody>
  </v-table>
</template>

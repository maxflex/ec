<script setup lang="ts">
interface FreeCabinet {
  cabinet: Cabinet
  free_until: string | null
  is_busy: boolean
}

const { indexPageData, items } = useIndex<FreeCabinet>(`free-cabinets`)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <v-table>
      <tbody>
        <tr v-for="item in items" :key="item.cabinet">
          <td width="180">
            {{ CabinetLabel[item.cabinet] }}
          </td>
          <td width="200">
            <UiCircle v-if="item.is_busy" color="error">
              занят
            </UiCircle>
            <UiCircle v-else color="success">
              свободен
            </UiCircle>
          </td>
          <td>
            <template v-if="!item.is_busy">
              <template v-if="item.free_until">
                до {{ formatTime(item.free_until) }}
              </template>
              <template v-else>
                до конца дня
              </template>
            </template>
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>

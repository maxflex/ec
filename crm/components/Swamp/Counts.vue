<script setup lang="ts">
import type { SwampCountsResource } from '.'

const { items } = defineProps<{
  items: SwampCountsResource[]
}>()

function sum(status: SwampStatus) {
  return items.reduce((carry, x) => carry + x.counts[status], 0)
}

function formatLabel(label: string) {
  return label.split(' + ').join('<br/>')
}
</script>

<template>
  <v-table height="calc(100vh - 81px)" class="swamp-counts">
    <thead>
      <tr>
        <th>
        </th>
        <th v-for="(label, status) in SwampStatusLabel" :key="status" width="140" v-html="formatLabel(label)">
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in items" :key="item.client.id">
        <td>
          <UiPerson :item="item.client" />
        </td>
        <td v-for="(_, status) in SwampStatusLabel" :key="status" width="140">
          {{ item.counts[status] || '' }}
        </td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <td></td>
        <td v-for="(_, status) in SwampStatusLabel" :key="status" width="140">
          {{ sum(status) || '' }}
        </td>
      </tr>
    </tfoot>
  </v-table>
</template>

<style lang="scss">
.swamp-counts {
  td,
  th {
    border-right: 1px solid rgb(var(--v-theme-border));
  }

  thead {
    position: sticky;
    top: 0;
    z-index: 2;
    th {
      vertical-align: top !important;
      background: white;
    }
  }
  tfoot {
    td:first-child {
      background: white;
      // background: rgb(var(--v-theme-bg));
    }
    tr {
      position: sticky;
      bottom: 0;
    }
    td {
      background: white;
      font-weight: bold;
      border-top: 1px solid rgb(var(--v-theme-border)) !important;
    }
  }
}
</style>

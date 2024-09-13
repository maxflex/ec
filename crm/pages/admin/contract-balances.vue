<script setup lang="ts">
import { descend, prop, sortBy } from 'rambda'

interface ContractBalance {
  id: number
  client: PersonResource
  company: Company
  latest_payment_date: string | null
  client_lessons: number
  active_version_sum: number
  contract_payments: number
  remainder: number
  to_pay: number
}

type ContractBalanceField = keyof ContractBalance

const filters = ref({
  year: currentAcademicYear(),
})

const sort = ref<{
  field: keyof ContractBalance
  direction: 'asc' | 'desc'
}>()

const { items, reloadData, indexPageData } = useIndex<ContractBalance>(
    `contract-balances`,
    {
      defaultFilters: filters.value,
    },
)

const tableHeader: Array<{
  title: string
  field: ContractBalanceField
}> = [
  { title: 'сумма в<br>договоре', field: 'active_version_sum' },
  { title: 'оплачено <br>по договору', field: 'contract_payments' },
  { title: 'начислено <br>за занятия', field: 'client_lessons' },
  { title: 'последний<br>платеж', field: 'latest_payment_date' },
  { title: 'остаток по<br>договору', field: 'remainder' },
  { title: 'неосвоенный<br>остаток', field: 'to_pay' },
]

function toggleSort(field: ContractBalanceField) {
  if (!sort.value || sort.value.field !== field) {
    sort.value = {
      field,
      direction: 'asc',
    }
  }
  else if (sort.value.direction === 'asc') {
    sort.value.direction = 'desc'
  }
  else {
    sort.value = undefined
  }
}

function getTotal(field: ContractBalanceField) {
  return items.value.reduce((carry, x) => carry + (x[field] as number), 0)
}

const sortedItems = computed(() => {
  if (!sort.value) {
    return items.value
  }

  const { field, direction } = sort.value

  // @ts-expect-error
  const sortFn = direction === 'asc' ? prop(field) : descend(prop(field))

  // @ts-expect-error
  return sortBy(sortFn, items.value)
})

watch(filters.value, reloadData)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Учебный год"
        density="comfortable"
      />
    </template>
    <v-table fixed-header height="calc(100vh - 81px)" class="contract-balances-table">
      <thead>
        <tr>
          <th />
          <th />
          <th
            v-for="h in tableHeader"
            :key="h.field"
            class="sortable"
            :class="{
              'sortable--desc': sort?.direction === 'desc',
            }"
            @click="toggleSort(h.field)"
          >
            <span v-html="h.title" />
            <v-icon v-if="sort?.field === h.field" icon="$collapse" />
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in sortedItems" :key="item.id">
          <td>
            №{{ item.id }}
          </td>
          <td>
            <RouterLink
              :to="{
                name: 'clients-id',
                params: { id: item.client.id },
              }"
            >
              {{ formatName(item.client) }}
            </RouterLink>
          </td>
          <td>
            {{ formatPrice(item.active_version_sum) }}
          </td>
          <td>
            {{ formatPrice(item.contract_payments) }}
          </td>
          <td>
            {{ formatPrice(item.client_lessons) }}
          </td>
          <td>
            <span v-if="item.latest_payment_date">
              {{ formatDate(item.latest_payment_date) }}
            </span>
          </td>
          <td>
            {{ formatPrice(item.remainder) }}
          </td>
          <td>
            {{ formatPrice(item.to_pay) }}
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td />
          <td />
          <td>
            {{ formatPrice(getTotal('active_version_sum')) }}
          </td>
          <td>
            {{ formatPrice(getTotal('contract_payments')) }}
          </td>
          <td>
            {{ formatPrice(getTotal('client_lessons')) }}
          </td>
          <td />
          <td>
            {{ formatPrice(getTotal('remainder')) }}
          </td>
          <td>
            {{ formatPrice(getTotal('to_pay')) }}
          </td>
        </tr>
      </tfoot>
    </v-table>
  </UiIndexPage>
</template>

<style lang="scss">
.contract-balances-table {
  tr {
    td,
    th {
      &:first-child {
        width: 100px;
      }
      &:nth-child(2) {
        width: 250px;
      }
      &:nth-child(3),
      &:nth-child(4),
      &:nth-child(5),
      &:nth-child(7) {
        width: 150px;
      }
      //&:not(:first-child):not(:last-child) {
      //  width: 150px;
      //}
      //&:nth-child(6),
      &:nth-child(7) {
        border-left: 1px solid rgb(var(--v-theme-border));
      }
    }
    td {
      &:nth-child(7),
      &:nth-child(8) {
        font-weight: bold !important;
      }
    }
  }
  tfoot td {
    font-weight: 500;
    border-top-width: 1px !important;
  }
}
</style>

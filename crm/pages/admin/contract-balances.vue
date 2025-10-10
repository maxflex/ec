<script setup lang="ts">
interface ContractBalance {
  id: number
  client: PersonResource
  company: Company
  latest_payment_date: string | null
  client_lessons: number
  active_version_sum: number
  contract_payments: number
  comments_count: number
  remainder: number
  to_pay: number
}

type ContractBalanceField = keyof ContractBalance

const filters = ref(loadFilters({
  year: currentAcademicYear(),
}))

const sort = ref<{
  field: keyof ContractBalance
  direction: 'asc' | 'desc'
}>()

const { items, indexPageData } = useIndex<ContractBalance>(
  `contract-balances`,
  filters,
)

const tableFields: Array<{
  title: string
  field: ContractBalanceField
}> = [
  { title: 'сумма в<br>договоре', field: 'active_version_sum' },
  { title: 'оплачено <br>по договору', field: 'contract_payments' },
  { title: 'начислено <br>за занятия', field: 'client_lessons' },
  { title: 'последний<br>платеж', field: 'latest_payment_date' },
  { title: 'баланс по<br>услугам', field: 'remainder' },
  { title: 'доплата по<br>договору', field: 'to_pay' },
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

  return [...items.value].sort((a, b) => {
    const fieldA = a[field] as number | string
    const fieldB = b[field] as number | string

    // Handle cases where field values might be non-truthy
    if (!fieldA && fieldB) {
      return 1 // Non-truthy values go last
    }
    if (fieldA && !fieldB) {
      return -1 // Non-truthy values go last
    }
    if (!fieldA && !fieldB) {
      return 0 // Both are non-truthy, consider them equal
    }

    // Ascending or descending order
    if (direction === 'asc') {
      return fieldA > fieldB ? 1 : fieldA < fieldB ? -1 : 0
    }
    else {
      return fieldA < fieldB ? 1 : fieldA > fieldB ? -1 : 0
    }
  })
})

watch(filters.value, () => (sort.value = undefined))
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
            v-for="h in tableFields"
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
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in sortedItems" :key="item.id">
          <td>
            №{{ item.id }}
          </td>
          <td>
            <RouterLink :to="{ name: 'clients-id', params: { id: item.client.id } }">
              {{ formatName(item.client) }}
            </RouterLink>
          </td>
          <td v-for="{ field } in tableFields" :key="field">
            {{ field === 'latest_payment_date'
              ? formatDate(item.latest_payment_date)
              : formatPrice(item[field] as number)
            }}
          </td>
          <td class="contract-balances-table__comment">
            <div>
              <CommentBtn
                color="gray"
                :size="42"
                :class="{ 'no-items': item.comments_count === 0 }"
                :count="item.comments_count"
                :entity-id="item.id"
                :entity-type="EntityTypeValue.client"
                extra
              />
            </div>
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td />
          <td />
          <td v-for="{ field } in tableFields" :key="field">
            {{ field === 'latest_payment_date' ? '' : formatPrice(getTotal(field)) }}
          </td>
          <td></td>
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
      &:nth-child(7),
      &:nth-child(9) {
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

  &__comment {
    width: 80px;
    position: relative;

    & > div {
      width: 44px;
      position: absolute;
      left: 8px;
      top: 8px;
    }
  }
}
</style>

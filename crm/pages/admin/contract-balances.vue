<script setup lang="ts">
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

const filters = ref({
  year: currentAcademicYear(),
})

const { items, reloadData, indexPageData } = useIndex<ContractBalance>(
    `contract-balances`,
    {
      defaultFilters: filters.value,
    },
)

function getTotal(field: keyof ContractBalance) {
  return items.value.reduce((carry, x) => carry + (x[field] as number), 0)
}

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
          <th>
            сумма <br>
            в договоре
          </th>
          <th>
            оплачено <br>
            по договору
          </th>
          <th>
            начислено <br>
            за занятия
          </th>
          <th>
            последний<br>
            платеж
          </th>
          <th>
            остаток по<br>
            договору
            <v-tooltip location="bottom">
              <template #activator="{ props }">
                <v-icon icon="$info" v-bind="props" />
              </template>
              сумма в договоре – оплачено по договору
            </v-tooltip>
          </th>
          <th>
            неосвоенный<br>
            остаток
            <v-tooltip location="bottom">
              <template #activator="{ props }">
                <v-icon icon="$info" v-bind="props" />
              </template>
              оплачено по договору – начислено за занятия
            </v-tooltip>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td>
            №{{ item.id }}
            <!--              {{ CompanyLabel[item.company] }} -->
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
  thead {
    .v-icon {
      font-size: 18px;
      opacity: 0.5;
      &:hover {
        opacity: 1;
      }
    }
  }
}
</style>

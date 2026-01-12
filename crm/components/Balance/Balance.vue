<script setup lang="ts">
const { contractId, teacherId, split, showTotals } = defineProps<{
  contractId?: number
  teacherId?: number
  split?: boolean
  showTotals?: boolean
}>()

interface Filters {
  year?: Year
  split?: number
}

const filters = ref<Filters>({
  year: undefined,
  split: undefined,
})

const { indexPageData, availableYears, items } = useIndex<Balance>(
  `balance`,
  filters,
  {
    loadAvailableYears: !!teacherId,
    staticFilters: {
      teacher_id: teacherId,
      contract_id: contractId,
    },
  },
)

const totals = computed(() => {
  if (!showTotals) {
    return null
  }
  const result = {
    lessons: 0,
    reports: 0,
    services: 0,
  }
  for (const item of items.value) {
    for (const i of item.items) {
      if (!i.is_confirmed) {
        continue
      }
      if (i.comment.startsWith('занятие')) {
        result.lessons += i.sum
      }
      else if (i.comment.startsWith('выплата')) {
        // result.payouts += i.sum
      }
      else if (i.comment.startsWith('отчет')) {
        result.reports += i.sum
      }
      else {
        result.services += i.sum
      }
    }
  }
  return result
})
</script>

<template>
  <UiIndexPage :data="indexPageData" class="balance">
    <template v-if="teacherId" #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
      <UiClearableSelect
        v-if="split"
        v-model="filters.split"
        density="comfortable"
        label="Разделение баланса"
        :items="yesNo('по занятиям', 'по остальным начислениям')"
      />
    </template>
    <template #buttons>
      <slot />
    </template>
    <Table class="balance-table">
      <TableRow v-for="item in items" :key="item.date">
        <TableCol :width="200">
          {{ formatDate(item.date) }}
        </TableCol>
        <TableCol class="balance-items">
          <table>
            <tbody>
              <tr>
                <td width="140" />
                <td width="140" />
                <td width="180">
                  {{ formatPrice(item.balance, true) }} руб.
                </td>
                <td />
              </tr>
              <tr
                v-for="(balanceItem, i) in item.items"
                :key="i"
              >
                <td>
                  <span
                    v-if="balanceItem.sum > 0"
                    :class="balanceItem.is_confirmed ? 'text-success' : 'text-gray'"
                  >
                    +{{ formatPrice(balanceItem.sum) }} руб.
                  </span>

                  <span v-else-if="balanceItem.sum === 0" class="text-success">
                    бесплатно
                  </span>
                </td>
                <td>
                  <span
                    v-if="balanceItem.sum < 0"
                    :class="balanceItem.is_confirmed ? 'text-error' : 'text-gray'"
                  >
                    {{ formatPrice(balanceItem.sum) }} руб.
                  </span>
                </td>
                <td />
                <td>
                  {{ balanceItem.comment }}
                </td>
              </tr>
            </tbody>
          </table>
        </TableCol>
      </TableRow>
      <TableRow v-if="totals" class="balance__totals">
        <TableCol class="font-weight-bold">
          Итого начислено
        </TableCol>
        <TableCol>
          занятия: {{ formatPrice(totals.lessons, true) }} руб.
        </TableCol>
        <TableCol>
          отчеты: {{ formatPrice(totals.reports, true) }} руб.
        </TableCol>
        <TableCol>
          допуслуги: {{ formatPrice(totals.services, true) }} руб.
        </TableCol>
      </TableRow>
    </Table>
  </UiIndexPage>
</template>

<style lang="scss">
.balance {
  &-items {
    flex: 1;
    table {
      border-collapse: collapse;
    }
  }
  &-table {
    font-size: 14px;
    & > div {
      padding: 12px 20px !important;
      align-items: flex-end !important;
    }
  }

  &__totals {
    position: sticky !important;
    bottom: 0;
    z-index: 1;
    background: rgb(var(--v-theme-bg));
    border-top: 1px solid rgb(var(--v-theme-border));
    gap: 60px !important;
    font-size: 16px !important;
    & > div {
      top: -2px;
      position: relative;
    }
  }
}
</style>

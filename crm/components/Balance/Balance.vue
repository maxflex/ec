<script setup lang="ts">
const { contractId, teacherId, split } = defineProps<{
  contractId?: number
  teacherId?: number
  split?: boolean
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
    <div class="table balance-table">
      <div v-for="item in items" :key="item.date">
        <div style="width: 200px">
          {{ formatDate(item.date) }}
        </div>
        <div class="balance-items">
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
                    class="text-success"
                  >
                    +{{ formatPrice(balanceItem.sum) }} руб.
                  </span>
                </td>
                <td>
                  <span
                    v-if="balanceItem.sum < 0"
                    class="text-error"
                  >
                    {{ formatPrice(balanceItem.sum) }} руб.
                  </span>
                  <span v-else-if="balanceItem.sum === 0" class="text-deepOrange">
                    бесплатно
                  </span>
                </td>
                <td />
                <td>
                  {{ balanceItem.comment }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
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
}
</style>

<script setup lang="ts">
const { contractId, teacherId } = defineProps<{
  contractId?: number
  teacherId?: number
}>()

const filters = ref<AvailableYearsFilter>({
  year: undefined,
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
      <AvailableYearsSelector2 v-model="filters.year" :items="availableYears" />
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
                  <span v-else-if="balanceItem.sum === 0" class="text-gray">
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

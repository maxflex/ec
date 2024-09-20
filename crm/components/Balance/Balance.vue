<script setup lang="ts">
const { contractId, teacherId } = defineProps<{
  contractId?: number
  teacherId?: number
}>()

const tabName = contractId ? 'ContractBalance' : 'TeacherBalance'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}, tabName))
const balance = ref<Balance[]>([])
const loading = ref(true)

async function loadData() {
  loading.value = true
  const { data } = await useHttp<Balance[]>(`balance`, {
    params: {
      year: teacherId ? filters.value.year : undefined,
      teacher_id: teacherId,
      contract_id: contractId,
    },
  })
  if (data.value) {
    balance.value = data.value
  }
  loading.value = false
}

const noData = computed(() => !loading.value && balance.value.length === 0)

watch(filters, (newVal) => {
  loadData()
  saveFilters(newVal, tabName)
}, { deep: true })

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }" :testy="true" class="balance">
    <template v-if="teacherId" #filters>
      <v-select
        v-model="filters.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
    </template>
    <div class="table balance-table">
      <div
        v-for="b in balance"
        :key="b.date"
      >
        <div style="width: 200px">
          {{ formatDate(b.date) }}
        </div>
        <div class="balance-items">
          <table>
            <tr>
              <td width="140" />
              <td width="140" />
              <td width="180">
                {{ formatPrice(b.balance) }} руб.
              </td>
              <td />
            </tr>
            <tr
              v-for="(balanceItem, i) in b.items"
              :key="i"
            >
              <td>
                <span
                  v-if="balanceItem.sum >= 0"
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
              </td>
              <td />
              <td>
                {{ balanceItem.comment }}
              </td>
            </tr>
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

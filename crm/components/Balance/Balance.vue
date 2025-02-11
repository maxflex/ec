<script setup lang="ts">
const { contractId, teacherId } = defineProps<{
  contractId?: number
  teacherId?: number
}>()

const balance = ref<Balance[]>([])
const loading = ref(true)
const availableYearsLoaded = ref(false)
const selectedYear = ref<Year>()

async function loadData() {
  loading.value = true
  const { data } = await useHttp<Balance[]>(
    `balance`,
    {
      params: {
        year: teacherId ? selectedYear.value : undefined,
        teacher_id: teacherId,
        contract_id: contractId,
      },
    },
  )
  if (data.value) {
    balance.value = data.value
  }
  loading.value = false
}

const noData = computed(() => !loading.value && balance.value.length === 0)

function onAvailableYearsLoaded() {
  availableYearsLoaded.value = true
  if (selectedYear.value) {
    loadData()
    watch(selectedYear, loadData)
  }
}

nextTick(() => contractId && loadData())
</script>

<template>
  <UiIndexPage :data="{ loading, noData }" :testy="true" class="balance">
    <template v-if="teacherId" #filters>
      <AvailableYearsSelector
        v-model="selectedYear"
        :teacher-id="teacherId"
        mode="teacher-balance"
        @loaded="onAvailableYearsLoaded()"
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
            <tbody>
              <tr>
                <td width="140" />
                <td width="140" />
                <td width="180">
                  {{ formatPrice(b.balance, true) }} руб.
                </td>
                <td />
              </tr>
              <tr
                v-for="(balanceItem, i) in b.items"
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

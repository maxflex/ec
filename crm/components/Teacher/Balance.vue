<script setup lang="ts">
const { teacher } = defineProps<{
  teacher: TeacherResource
}>()

// const year = ref<Year>(currentStudyYear())
const year = ref<Year>(2023)
const balance = ref<Balance[]>([])
const loading = ref(true)

async function loadData() {
  loading.value = true
  const { data } = await useHttp<Balance[]>(`balance/teacher/${teacher.id}`, {
    params: {
      year: year.value,
    },
  })
  if (data.value) {
    balance.value = data.value
  }
  loading.value = false
}

watch(year, loadData)

nextTick(loadData)
</script>

<template>
  <div class="balance">
    <div class="filters">
      <div class="filters-inputs">
        <v-select
          v-model="year"
          label="Учебный год"
          :items="selectItems(YearLabel)"
          density="comfortable"
        />
      </div>
    </div>
    <UiLoaderr v-if="loading" />
    <div
      v-else-if="balance.length"
      class="table"
    >
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
    <div
      v-else
      class="balance-empty"
    >
      нет данных
    </div>
  </div>
</template>

<style lang="scss">
.balance {
  display: flex;
  flex-direction: column;
  flex: 1;
  & > div {
    &:last-child {
      flex: 1;
    }
  }
  &-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgb(var(--v-theme-gray));
  }
  &-items {
    flex: 1;
    table {
      border-collapse: collapse;
      tr {
        td {
        }
      }
    }
  }
  .table {
    font-size: 14px;
    & > div {
      padding: 12px 20px !important;
      align-items: flex-end !important;
    }
  }
  // padding: 40px 20px;
}
</style>

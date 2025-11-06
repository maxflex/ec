<script setup lang="ts">
const selected = ref(0)
const { items, indexPageData } = useIndex<BillingResource>(`billing`)

const selectedContract = computed(() => items.value[selected.value])

function totalSum(payments: Array<{ sum: number, is_return?: boolean }>) {
  return payments.reduce(
    (carry, e) => carry + e.sum * (e.is_return ? -1 : 1),
    0,
  )
}

// остаток по договору - осталось к оплате
const toPayLeft = computed<number>(() =>
  totalSum(selectedContract.value.version.payments) - totalSum(selectedContract.value.payments),
)

const isSbpPaymentAvailable = computed<boolean>(() => {
  if (selectedContract.value.year < currentAcademicYear()) {
    return false
  }
  // оплата по QR доступна только для ИП
  if (selectedContract.value.company !== 'ip') {
    return false
  }
  if (toPayLeft.value <= 0) {
    return false
  }
  return selectedContract.value.version.has_courses_program
})
</script>

<template>
  <UiPageTitle>
    Оплата обучения
  </UiPageTitle>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-chip
        v-for="(contract, i) in items"
        :key="contract.id"
        label
        :class="selected === i ? 'bg-primary' : undefined"
        size="large"
        @click="selected = i"
      >
        <div>
          Договор №{{ contract.id }}
        </div>
        <div>
          на {{ YearLabel[contract.year] }}
        </div>
      </v-chip>
    </template>
    <div class="table">
      <div>
        <div class="font-weight-bold">
          График платежей
        </div>
      </div>
      <div v-for="p in selectedContract.version.payments" :key="p.id">
        <div>
          {{ formatDate(p.date) }}
        </div>
        <div>{{ formatPrice(p.sum) }} руб.</div>
      </div>
      <div>
        <div></div>
        <div>
          <b>
            {{ formatPrice(totalSum(selectedContract.version.payments)) }} руб.
          </b>
        </div>
      </div>
    </div>
    <div v-if="selectedContract.payments.length" class="table">
      <div>
        <b>Оплачено</b>
      </div>
      <div v-for="p in selectedContract.payments" :key="p.id">
        <div>
          {{ formatDate(p.date) }}
        </div>
        <div>
          <span v-if="p.is_return" class="text-red">
            {{ formatPrice(p.sum) }} руб. (возврат)
          </span>
          <span v-else>
            {{ formatPrice(p.sum) }} руб.
          </span>
        </div>
      </div>
      <div>
        <div></div>
        <div>
          <b>
            {{ formatPrice(totalSum(selectedContract.payments)) || 0 }} руб.
          </b>
        </div>
      </div>
    </div>

    <div class="table page-billing__total">
      <div></div>
      <div>
        <div>
          Итого остаток<br />
          по договору
        </div>
        <div>
          <b :class="{ 'text-gray': toPayLeft <= 0 }">
            {{ formatPrice(toPayLeft, true) }} руб.
          </b>
        </div>
      </div>
    </div>
    <BillingSbp v-if="isSbpPaymentAvailable" :selected-contract="selectedContract" />
  </UiIndexPage>
</template>

<style lang="scss">
.page-billing {
  .filters {
    .v-chip {
      &__content {
        display: flex;
        flex-direction: column;
        line-height: 14px;
        text-align: center;
        & > div {
          &:first-child {
            font-size: 14px;
            font-weight: 500;
          }
          &:last-child {
            font-size: 12px;
            opacity: 0.8;
          }
        }
      }
      &__underlay {
        display: none !important;
      }
    }
  }
  .table {
    & > div {
      &:not(:first-child) {
        & > div {
          &:first-child {
            width: 150px;
          }
        }
      }
    }
  }
  &__total {
    font-weight: bold;
    font-size: 16px;
    padding: 20px 0 20px;
    line-height: 20px;
    & > div:first-child {
      display: none;
    }
  }
}
</style>

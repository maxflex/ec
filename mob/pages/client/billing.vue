<script setup lang="ts">
const selected = ref(0)
const amount = ref('')
const loading = ref(false)

const { items, indexPageData } = useIndex<BillingResource>(`billing`)

const selectedContract = computed(() => items.value[selected.value])

function totalSum(payments: Array<{ sum: number, is_return?: boolean }>) {
  return payments.reduce(
    (carry, e) => carry + e.sum * (e.is_return ? -1 : 1),
    0,
  )
}

async function pay() {
  if (!amount.value) {
    return
  }
  loading.value = true
  const { data } = await useHttp<{ url: string }>(
    `sbp`,
    {
      method: 'POST',
      body: {
        amount: amount.value,
        contract_id: selectedContract.value.id,
      },
    },
  )
  if (data.value) {
    window.open(data.value.url, '_blank')
  }
  loading.value = false
}
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
          на {{ formatYear(contract.year) }}
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
            {{ formatPrice(totalSum(selectedContract.payments)) }} руб.
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
          <b v-if="totalSum(selectedContract.version.payments) - totalSum(selectedContract.payments)">
            {{ formatPrice(totalSum(selectedContract.version.payments) - totalSum(selectedContract.payments)) }}
            руб.
          </b>
          <b v-else class="text-gray">
            0 руб.
          </b>
        </div>
      </div>
    </div>
    <div class="billing-qr">
      <UiPageTitle>
        Оплата через СБП
      </UiPageTitle>
      <div>
        <v-text-field
          v-model="amount"
          v-maska="{ mask: '######' }"
          hide-spin-buttons
          label="Сумма"
          suffix="руб."
          type="number"
          density="comfortable"
          bg-color="white"
        />
      </div>
      <v-btn color="primary" :loading="loading" @click="pay()">
        Оплатить
      </v-btn>
    </div>
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

.billing-qr {
  margin-top: 40px;
  padding: 20px 20px 40px;
  background: rgba(var(--v-theme-primary), 0.1);
  border-radius: 8px 8px 0 0;
  display: flex;
  flex-direction: column;
  gap: 20px;
  .page-title {
    padding: 0 !important;
    text-align: center;
  }
}
</style>

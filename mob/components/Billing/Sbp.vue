<script setup lang="ts">
import { cloneDeep } from 'lodash-es'

const { selectedContract } = defineProps<{ selectedContract: BillingResource }>()
const phoneMask = { mask: '+7 (###) ###-##-##' }

const payload = reactive({
  amount: '',
  number: undefined,
})

const phones = ref<PhoneResource[]>([])
const loading = ref(false)

async function loadData() {
  const { data } = await useHttp<ApiResponse<PhoneResource>>(
    `phones`,
    {
      params: {
        contract_id: selectedContract.id,
      },
    },
  )
  phones.value = data.value!.data
}

async function pay() {
  if (!payload.amount || !payload.number) {
    return
  }
  loading.value = true
  const { data, error } = await useHttp<{ url: string }>(
    `sbp`,
    {
      method: 'POST',
      body: {
        ...cloneDeep(payload),
        contract_id: selectedContract.id,
      },
    },
  )
  if (error.value) {
    loading.value = false
    return
  }
  window.location = data.value!.url as string & Location
}

nextTick(loadData)
</script>

<template>
  <div class="billing-sbp">
    <div class="billing-sbp__title">
      <img src="/public/img/sbp.svg" />
      Оплатить через СБП
    </div>
    <div>
      <v-text-field
        v-model="payload.amount"
        v-maska="{ mask: '######' }"
        hide-spin-buttons
        label="Сумма"
        suffix="руб."
        type="number"
        density="comfortable"
        bg-color="white"
      />
    </div>
    <div>
      <v-select
        v-model="payload.number"
        v-maska="{ mask: phoneMask }"
        item-value="number"
        label="Куда отправить чек"
        :items="phones"
        density="comfortable"
        bg-color="white"
      >
        <template #selection="{ item }">
          {{ formatPhone(item.value) }}
        </template>
        <template #item="{ item, props }">
          <v-list-item v-bind="props">
            <template #prepend />
            <template #title>
              {{ formatPhone(item.value) }}
            </template>
          </v-list-item>
        </template>
      </v-select>
    </div>
    <v-btn color="primary" :loading="loading" @click="pay()">
      Оплатить
    </v-btn>
  </div>
</template>

<style lang="scss">
.billing-sbp {
  margin-top: 40px;
  padding: 20px 20px 100px;
  background: rgba(var(--v-theme-primary), 0.1);
  // background-color: rgb(var(--v-theme-bg));
  // border-radius: 8px 8px 0 0;
  display: flex;
  flex-direction: column;
  gap: 20px;

  button {
    margin-top: 20px;
  }

  &__title {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    gap: 10px;
    img {
      height: 30px;
    }
  }
}
</style>

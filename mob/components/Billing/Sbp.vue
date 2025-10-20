<script setup lang="ts">
const { selectedContract } = defineProps<{ selectedContract: BillingResource }>()
const amount = ref('')
const loading = ref(false)

async function pay() {
  if (!amount.value) {
    return
  }
  loading.value = true
  const { data, error } = await useHttp<{ url: string }>(
    `sbp`,
    {
      method: 'POST',
      body: {
        amount: amount.value,
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
</script>

<template>
  <div class="billing-sbp">
    <div class="billing-sbp__title">
      <img src="/public/img/sbp.svg" />
      Оплатить через СБП
    </div>
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
  & > div:nth-child(2) {
    margin: 30px 0 20px;
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

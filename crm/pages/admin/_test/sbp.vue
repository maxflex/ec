<script setup lang="ts">
interface YooKassaPayment {
  confirmation: {
    confirmation_url: string
  }
}

const result = ref<YooKassaPayment>()
const err = ref()
const loading = ref(false)
const amount = ref('')
const {width} = useDialog('default')

async function pay() {
  loading.value = true
  const { data, error } = await useHttp<YooKassaPayment>('sbp', {
    method: 'POST',
    body: {
      amount: amount.value,
    },
  })
  loading.value = false
  if (error.value) {
    err.value = error.value.data
  }
  if (data.value) {
    result.value = data.value
    window.open(result.value.confirmation.confirmation_url, '_blank')
  }
}
</script>

<template>
  <div class="dialog-body" :style="{width: `${width}px`}">
    <div>
      <v-text-field
        v-model="amount"
        hide-spin-buttons
        label="Сумма"
        suffix="руб."
        type="number"
        v-maska="{ mask: '######' }"
      />
    </div>
    <v-btn color="primary" :loading="loading" @click="pay()">
      оплата сбп
    </v-btn>
    <pre>
      {{ result }}
    </pre>
    <pre v-if="err" class="text-error">
      {{ err }}
    </pre>
  </div>
</template>

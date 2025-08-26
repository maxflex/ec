<script lang="ts">
import type { OtherPaymentMethod } from '~/components/OtherPayment'
import { OtherPaymentMethodLabel } from '~/components/OtherPayment'

interface Filters {
  method: OtherPaymentMethod[]
  is_confirmed?: number
  is_return?: number
}

const filterDefaults: Filters = {
  method: [],
}

export default {
  label: 'Прочие платежи',
  width: 130,
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
</script>

<template>
  <div>
    <UiMultipleSelect
      v-model="filters.method"
      label="Метод оплаты"
      :items="selectItems(OtherPaymentMethodLabel)"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.is_return"
      label="Операция"
      :items="yesNo('возврат', 'платеж', true)"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.is_confirmed"
      label="Подтверждение платежа"
      :items="yesNo()"
    />
  </div>
</template>

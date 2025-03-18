<script lang="ts">
interface Filters {
  year: Year[]
  method: ClientPaymentMethod[]
  company?: Company
  is_confirmed?: number
  is_return?: number
}

const filterDefaults: Filters = {
  year: [],
  method: [],
}

export default {
  label: 'Платежи по клиентам',
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
    <UiMultipleSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.method"
      label="Метод оплаты"
      :items="selectItems(ClientPaymentMethodLabel)"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.company"
      label="Компания"
      :items="selectItems(CompanyLabel)"
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

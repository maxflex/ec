<script lang="ts" setup>
interface Filters {
  year: Year[]
  type?: number
  method: ContractPaymentMethod[]
  company?: Company
  is_confirmed?: number
  is_return?: number
}

const filters = ref<Filters>({
  year: [],
  method: [],
})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Платежи клиентов',
  width: 130,
  filters: {
    method: [],
  },
}
</script>

<template>
  <div>
    <UiMultipleSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.type"
      :items="yesNo('по договорам', 'по клиентам')"
      label="Тип платежа"
    />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.method"
      label="Метод оплаты"
      :items="selectItems(ContractPaymentMethodLabel)"
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
      :items="yesNo('возвраты', 'платежи', true)"
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

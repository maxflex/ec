<script lang="ts" setup>
interface Filters {
  year: Year[]
  method: TeacherPaymentMethod[]
  is_confirmed?: number
}

const filters = ref<Filters>({
  year: [],
  method: [],
})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Платежи препод',
  width: 130,
  filters: {
    year: [],
    method: [],
  },
}
</script>

<template>
  <div>
    <UiMultipleSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.method"
      label="Метод оплаты"
      :items="selectItems(TeacherPaymentMethodLabel)"
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

<script lang="ts">
interface Filters {
  // year: Year[]
  method: TeacherPaymentMethod[]
  is_confirmed?: number
}

const filterDefaults: Filters = {
  method: [],
}

export default {
  label: 'Платежи по преподавателям',
  width: 130,
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
</script>

<template>
  <!-- <div>
    <UiMultipleSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" />
  </div> -->
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

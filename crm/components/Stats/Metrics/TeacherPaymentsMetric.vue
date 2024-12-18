<script lang="ts" setup>
interface Filters {
  year?: Year
  method?: TeacherPaymentMethod
  is_confirmed?: number
}

const filters = ref<Filters>({ })

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Платежи препод',
  width: 130,
  filters: {},
}
</script>

<template>
  <div>
    <UiClearableSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.method"
      label="Метод"
      :items="selectItems(TeacherPaymentMethodLabel)"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.is_confirmed"
      label="Подтверждён"
      :items="yesNo()"
    />
  </div>
</template>

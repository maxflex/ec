<script lang="ts" setup>
interface Filters {
  year?: Year
  is_return?: number
}

const filters = ref<Filters>({})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Платежи по договорам',
}
</script>

<template>
  <div>
    <UiClearableSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.is_return"
      :items="yesNo('только возвраты', 'только платежи', true)"
      label="Тип"
    />
  </div>
</template>

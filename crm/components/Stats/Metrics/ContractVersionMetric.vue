<script lang="ts" setup>
interface Filters {
  version?: number
  year?: Year
  company?: Company
  direction: Direction[]
}

const filters = ref<Filters>({
  direction: [],
})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Сумма версий',
  filters: {
    direction: [],
  },
}
</script>

<template>
  <div>
    <UiClearableSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" />
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
      v-model="filters.version"
      label="Версия договора"
      :items="yesNo('только первая', 'только изменения')"
    />
  </div>
  <div>
    <UiMultipleSelect v-model="filters.direction" :items="selectItems(DirectionLabel)" label="Направления" />
  </div>
</template>

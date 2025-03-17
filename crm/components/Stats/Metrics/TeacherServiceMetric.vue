<script lang="ts" setup>
interface Filters {
  year: Year[]
}

const filters = ref<Filters>({
  year: [],
})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Сумма допуслуг',
  width: 130,
  filters: {
    year: [],
  },
}
</script>

<template>
  <div>
    <!-- <UiMultipleSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" /> -->
  </div>
</template>

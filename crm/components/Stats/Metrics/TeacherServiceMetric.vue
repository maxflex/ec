<script lang="ts" setup>
interface Filters {
  year?: Year
}

const filters = ref<Filters>({ })

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Допуслуги препод',
  width: 130,
  filters: {
    method: [],
  },
}
</script>

<template>
  <div>
    <UiClearableSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" />
  </div>
</template>

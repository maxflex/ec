<script lang="ts">
interface Filters {
}

const filterDefaults: Filters = {

}

export default {
  label: 'Сумма допуслуг',
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
    <!-- <UiMultipleSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" /> -->
  </div>
</template>

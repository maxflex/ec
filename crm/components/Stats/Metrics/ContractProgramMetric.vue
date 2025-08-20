<script lang="ts">
interface Filters {
  year: Year[]
  program: Program[]
}

const filterDefaults: Filters = {
  year: [],
  program: [],
}

export default {
  label: 'Программы договоров',
  width: 150,
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
    <ProgramSelector v-model="filters.program" multiple density="default" :clearable="false" />
  </div>
</template>

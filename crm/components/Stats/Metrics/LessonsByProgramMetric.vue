<script lang="ts">
interface Filters {
  program: Program[]
}

const filterDefaults: Filters = {
  program: [],
}

export default {
  label: 'Уроки по программе',
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
</script>

<template>
  <div>
    <ProgramSelector v-model="filters.program" multiple density="default" :clearable="false" />
  </div>
</template>

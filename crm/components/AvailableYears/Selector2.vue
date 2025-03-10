<script setup lang="ts">
const { items } = defineProps<{
  items: Year[] | undefined
}>()
const model = defineModel<Year | undefined>({ required: true })

const availableYears = computed(() => {
  if (!Array.isArray(items)) {
    return selectItems2(YearLabel)
  }
  return items.map(year => ({
    value: year,
    title: YearLabel[year],
  }))
})

const loading = computed(() => !Array.isArray(items))
const disabled = computed(() => !loading.value && items!.length <= 1)
</script>

<template>
  <!-- :loading="loading" -->
  <v-select
    :disabled="disabled"
    :items="availableYears"
    :model-value="model || currentAcademicYear()"
    label="Учебный год"
    density="comfortable"
    @update:model-value="year => (model = year)"
  />
</template>

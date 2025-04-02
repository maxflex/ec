<script setup lang="ts">
const { items } = defineProps<{
  items: Year[] | undefined
}>()
const model = defineModel<Year | null>({ required: true })

const availableYears = computed(() => {
  if (!Array.isArray(items)) {
    return []
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
  <UiChipSelector
    v-model="model"
    :disabled="loading || disabled"
    :items="availableYears"
    :label="YearLabel[currentAcademicYear()]"
  />
</template>

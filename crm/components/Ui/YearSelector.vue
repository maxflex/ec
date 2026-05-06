<script setup lang="ts">
const { years } = defineProps<{
  years?: Year[]
}>()
const model = defineModel<Year>({ required: true })

const items = computed(() => {
  if (!years) {
    return selectItems(YearLabel)
  }

  // Для отдельных страниц можно зафиксировать короткий список учебных годов.
  return years.map(year => ({
    value: year,
    title: YearLabel[year],
  }))
})
</script>

<template>
  <v-select
    v-bind="$attrs"
    v-model="model"
    :items="items"
    label="Учебный год"
  />
</template>

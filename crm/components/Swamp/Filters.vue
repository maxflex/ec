<script lang="ts" setup>
export interface SwampFilters {
  year: Year
  program: Program[]
}

const { disabled } = defineProps<{
  disabled?: boolean
}>()
const model = defineModel<SwampFilters>({ required: true })
</script>

<template>
  <v-select
    v-model="model.year"
    label="Учебный год"
    :items="selectItems(YearLabel)"
    density="comfortable"
    :disabled="disabled"
  />
  <ProgramSelector
    v-model="model.program"
    multiple
    :items="selectItems(ProgramLabel)"
    density="comfortable"
    :disabled="disabled"
  />
</template>

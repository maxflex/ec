<script lang="ts" setup>
export interface Filters {
  year: Year
  program?: Program
  status?: SwampFilterStatus
}
const { disabled } = defineProps<{
  disabled?: boolean
}>()
const model = defineModel<Filters>({ required: true })
</script>

<template>
  <v-select
    v-model="model.year"
    label="Учебный год"
    :items="selectItems(YearLabel)"
    density="comfortable"
    :disabled="disabled"
  />
  <UiClearableSelect
    v-model="model.program"
    label="Программа"
    :items="selectItems(ProgramLabel)"
    density="comfortable"
    :disabled="disabled"
  />
  <UiClearableSelect
    v-model="model.status"
    label="Статус"
    :items="selectItems(SwampFilterStatusLabel)"
    :disabled="disabled"
    density="comfortable"
    expand
  />
</template>

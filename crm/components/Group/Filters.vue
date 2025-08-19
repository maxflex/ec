<script lang="ts" setup>
export interface GroupFilters {
  year: Year
  program: Program[]
  teacher_id?: number
  contract_date?: number
}

const { disabled } = defineProps<{
  disabled?: boolean
}>()
const model = defineModel<GroupFilters>({ required: true })
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
    :disabled="disabled"
  />
  <UiClearableSelect
    v-model="model.contract_date"
    :items="yesNo()"
    label="Договор заключен"
    density="comfortable"
    :disabled="disabled"
  />
</template>

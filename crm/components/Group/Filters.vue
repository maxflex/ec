<script lang="ts" setup>
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
  <UiClearableSelect
    v-model="model.program"
    label="Программа"
    :items="selectItems(ProgramLabel)"
    density="comfortable"
    :disabled="disabled"
  />
</template>

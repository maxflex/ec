<script lang="ts" setup>
export interface Filters {
  program?: Program
}

const model = defineModel<Filters>({ required: true })
</script>

<template>
  <UiClearableSelect
    v-model="model.program"
    label="Программа"
    :items="selectItems(ProgramShortLabel)"
    density="comfortable"
  />
</template>

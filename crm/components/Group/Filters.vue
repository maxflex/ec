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
  <ProgramSelector
    v-model="model.program"
    multiple
    :disabled="disabled"
  />
  <TeacherSelector
    v-model="model.teacher_id"
    density="comfortable"
    :disabled="disabled"
  />
</template>

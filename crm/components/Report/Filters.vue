<script lang="ts" setup>
export interface ReportFilters {
  year: Year
  program?: Program
  requirement?: ReportRequirement
  status?: ReportStatus
  teacher_id?: number
}

const model = defineModel<ReportFilters>({ required: true })
</script>

<template>
  <v-select
    v-model="model.year"
    label="Учебный год"
    :items="selectItems(YearLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.requirement"
    label="Тип"
    :items="selectItems(ReportRequirementLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.status"
    label="Статус"
    :items="selectItems(ReportStatusLabel)"
    density="comfortable"
  />
  <TeacherSelector
    v-model="model.teacher_id"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.program"
    label="Программа"
    :items="selectItems(ProgramLabel)"
    density="comfortable"
  />
</template>

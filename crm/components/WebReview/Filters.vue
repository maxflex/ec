<script lang="ts" setup>
const model = defineModel<WebReviewFilters>({ required: true })
</script>

<template>
  <UiMultipleSelect
    v-model="model.program"
    :items="selectItems(ProgramLabel)"
    label="Программы"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.has_exam_scores"
    :items="yesNo()"
    density="comfortable"
    label="Присвоен к оценке"
  />
</template>

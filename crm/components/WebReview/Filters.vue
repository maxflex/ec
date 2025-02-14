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
    v-model="model.exam_scores"
    :items="selectItems(WebReviewExamScoreFilterLabel)"
    density="comfortable"
    label="Оценка"
  />
</template>

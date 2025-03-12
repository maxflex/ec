<script lang="ts" setup>
export interface WebReviewFilters {
  program: Program[]
  // exam_scores: number
  is_published?: number
}

const model = defineModel<WebReviewFilters>({ required: true })
</script>

<template>
  <UiMultipleSelect
    v-model="model.program"
    :items="selectItems(ProgramLabel)"
    label="Программы"
    density="comfortable"
  />
  <!-- <UiClearableSelect
    v-model="model.exam_scores"
    :items="yesNo('есть баллы', 'нет баллов')"
    density="comfortable"
    label="Баллы"
  /> -->
  <UiClearableSelect
    v-model="model.is_published"
    label="Публикация"
    :items="yesNo('опубликован', 'не опубликован')"
    density="comfortable"
  />
</template>

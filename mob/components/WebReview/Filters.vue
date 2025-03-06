<script lang="ts" setup>
const WebReviewExamScoreFilterLabel = {
  notExists: 'нет доступных баллов',
  existsNotSelected: 'есть доступные + ничего не выбрано',
  existsSelected: 'есть доступные + есть выбранные',
} as const

export interface WebReviewFilters {
  program: Program[]
  exam_scores?: keyof typeof WebReviewExamScoreFilterLabel
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
  <UiClearableSelect
    v-model="model.exam_scores"
    :items="selectItems(WebReviewExamScoreFilterLabel)"
    density="comfortable"
    label="Балл"
  />
  <UiClearableSelect
    v-model="model.is_published"
    label="Публикация"
    :items="yesNo('опубликован', 'не опубликован')"
    density="comfortable"
  />
</template>

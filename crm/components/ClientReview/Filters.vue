<script lang="ts" setup>
const ClientReviewExamScoreFilterLabel = {
  notExists: 'нет баллов',
  existsNotAvailable: 'есть баллы, но нет доступных',
  existsAvailable: 'есть баллы + есть доступные',
} as const

export interface ClientReviewFilters {
  requirement?: number
  program?: Program
  rating?: number
  web_review_exists?: number
  exam_scores?: keyof typeof ClientReviewExamScoreFilterLabel
  year?: Year
}

const model = defineModel<ClientReviewFilters>({ required: true })
</script>

<template>
  <UiClearableSelect
    v-model="model.year"
    label="Учебный год"
    density="comfortable"
    :items="selectItems(YearLabel)"
  />
  <UiClearableSelect
    v-model="model.requirement"
    label="Требование отзыва"
    :items="yesNo('созданные', 'требуется создание')"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.web_review_exists"
    label="Отзыв на сайте"
    :items="yesNo('нет ни одного', 'есть минимум 1 отзыв')"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.program"
    label="Программа"
    :items="selectItems(ProgramLabel)"
    density="comfortable"
  />

  <UiClearableSelect
    v-model="model.exam_scores"
    :items="selectItems(ClientReviewExamScoreFilterLabel)"
    density="comfortable"
    label="Баллы"
  />
  <UiClearableSelect
    v-model="model.rating"
    label="Рейтинг"
    density="comfortable"
    :items="[5, 4, 3, 2, 1].map(e => ({
      value: e,
      title: `${e}`,
    }))"
  />
</template>

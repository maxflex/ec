<script lang="ts" setup>
export interface ClientReviewFilters {
  requirement?: number
  program?: Program
  rating?: number
  web_review_exists?: number
  exam_scores_exists?: number
  year?: Year
  is_marked?: number
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
    v-model="model.exam_scores_exists"
    label="Баллы"
    :items="yesNo('нет ни одного', 'есть минимум 1 балл')"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.program"
    label="Программа"
    :items="selectItems(ProgramLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.is_marked"
    label="Проводка"
    :items="yesNo('проведено', 'не проведено')"
    density="comfortable"
  />
</template>

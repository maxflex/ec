<script lang="ts" setup>
const model = defineModel<ClientReviewFilters>({ required: true })
</script>

<template>
  <UiClearableSelect
    v-model="model.type"
    label="Тип"
    :items="yesNo('созданные', 'требуется создание')"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.program"
    label="Программа"
    :items="selectItems(ProgramLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.rating"
    label="Оценка"
    density="comfortable"
    :items="[5, 4, 3, 2, 1].map(e => ({
      value: e,
      title: `${e}`,
    }))"
  />
  <TeacherSelector
    v-model="model.teacher_id"
    label="Преподаватель"
    density="comfortable"
  />
</template>

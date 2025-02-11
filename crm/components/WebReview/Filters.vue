<script lang="ts" setup>
const model = defineModel<WebReviewFilters>({ required: true })
</script>

<template>
  <UiClearableSelect
    v-model="model.has_exam_score"
    :items="yesNo()"
    density="comfortable"
    label="Присвоен к оценке"
  />
</template>

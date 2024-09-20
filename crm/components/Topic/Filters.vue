<script lang="ts" setup>
const model = defineModel<TopicFilters>({ required: true })
</script>

<template>
  <v-select
    v-model="model.year"
    label="Учебный год"
    :items="selectItems(YearLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.is_topic_verified"
    :items="yesNo('промодерировано', 'не промодерировано')"
    label="Модерация"
    density="comfortable"
  />
</template>

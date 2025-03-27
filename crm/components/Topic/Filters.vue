<script lang="ts" setup>
const StatusLabel = {
  noTopic: 'тема не установлена',
  notVerified: 'тема не подтверждена',
  verified: 'тема подтверждена',
} as const

export interface TopicFilters {
  year: Year
  status?: keyof typeof StatusLabel
}

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
    v-model="model.status"
    :items="selectItems(StatusLabel)"
    label="Статус"
    density="comfortable"
  />
</template>

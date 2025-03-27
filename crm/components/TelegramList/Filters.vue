<script setup lang="ts">
export interface TelegramListFilters {
  status?: TelegramListStatus
}

const model = defineModel<TelegramListFilters>({ required: true })
</script>

<template>
  <UiClearableSelect
    v-model="model.status"
    :items="selectItems(TelegramListStatusLabel)"
    label="Статус"
    density="comfortable"
  />
</template>

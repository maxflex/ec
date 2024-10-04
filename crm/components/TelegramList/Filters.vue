<script setup lang="ts">
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

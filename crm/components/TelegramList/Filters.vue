<script setup lang="ts">
const model = defineModel<TelegramListFilters>({ required: true })
</script>

<template>
  <UiClearableSelect
    v-model="model.is_sent"
    :items="yesNo('да', 'нет')"
    label="Отправлено"
    density="comfortable"
  />
</template>

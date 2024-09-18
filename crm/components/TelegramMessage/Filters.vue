<script lang="ts" setup>
const model = defineModel<TelegramMessageFilters>({ required: true })
</script>

<template>
  <UiClearableSelect
    v-model="model.template"
    label="Шаблон"
    :items="selectItems(TelegramTemplateLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.type"
    label="Тип"
    :items="yesNo('групповые', 'личные')"
    density="comfortable"
  />
</template>

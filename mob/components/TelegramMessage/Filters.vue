<script lang="ts" setup>
const model = defineModel<TelegramMessageFilters>({ required: true })
</script>

<template>
  <UiClearableSelect
    v-model="model.status"
    label="Статус доставки"
    :items="yesNo('доставлено', 'не доставлено')"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.template"
    label="Шаблон"
    :items="selectItems(TelegramTemplateLabel)"
    density="comfortable"
  />
</template>

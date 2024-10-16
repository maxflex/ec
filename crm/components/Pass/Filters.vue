<script lang="ts" setup>
const model = defineModel<PassFilters>({ required: true })
</script>

<template>
  <UiClearableSelect
    v-model="model.status"
    label="Статус"
    :items="selectItems(PassStatusLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.type"
    label="Тип"
    :items="selectItems(PassTypeLabel)"
    density="comfortable"
  />
</template>

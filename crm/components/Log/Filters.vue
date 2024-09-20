<script lang="ts" setup>
const model = defineModel<LogFilters>({ required: true })
const rowId = ref(model.value.row_id)
</script>

<template>
  <UiClearableSelect
    v-model="model.type"
    label="Действие"
    :items="selectItems(LogTypeLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.table"
    label="Таблица"
    :items="selectItems(LogTableLabel)"
    density="comfortable"
  />
  <v-text-field
    v-model="rowId"
    label="ID"
    density="comfortable"
    hide-spin-buttons
    type="number"
    @keydown.enter="model.row_id = rowId"
  />
</template>

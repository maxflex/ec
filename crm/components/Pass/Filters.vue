<script lang="ts" setup>
export interface PassFilters {
  status?: PassStatus
  type?: PassType
  direction: Direction[]
}

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
  <UiMultipleSelect
    v-model="model.direction"
    label="Направление"
    :items="selectItems(DirectionLabel)"
    density="comfortable"
  />
</template>

<script lang="ts" setup>
export interface PassFilters {
  status?: PassStatus
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
  <UiMultipleSelect
    v-model="model.direction"
    label="Направление"
    :items="selectItems(DirectionLabel)"
    density="comfortable"
  />
</template>

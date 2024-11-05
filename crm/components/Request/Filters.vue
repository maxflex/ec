<script lang="ts" setup>
const model = defineModel<RequestFilters>({ required: true })
</script>

<template>
  <UiClearableSelect
    v-model="model.status"
    label="Статус"
    :items="selectItems(RequestStatusLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.direction"
    label="Направление"
    :items="selectItems(DirectionLabel)"
    density="comfortable"
  />
</template>

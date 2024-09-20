<script lang="ts" setup>
const model = defineModel<ClientFilters>({ required: true })
const q = ref(model.value.q)
</script>

<template>
  <v-text-field
    v-model="q"
    label="Имя"
    density="comfortable"
    @keydown.enter="model.q = q"
  />
  <v-select
    v-model="model.year"
    label="Договоры"
    :items="selectItems(YearLabel)"
    density="comfortable"
  />
</template>

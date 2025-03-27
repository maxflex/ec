<script lang="ts" setup>
export interface ClientFilters {
  q: string
  year: Year
}

const model = defineModel<ClientFilters>({ required: true })
const q = ref(model.value.q)
</script>

<template>
  <v-select
    v-model="model.year"
    label="Договоры"
    :items="selectItems(YearLabel)"
    density="comfortable"
  />
  <v-text-field
    v-model="q"
    label="Имя"
    density="comfortable"
    @keydown.enter="model.q = q"
  />
</template>

<script setup lang="ts">
const PeopleSelectorModeLabel = {
  clients: 'клиенты',
  teachers: 'преподаватели',
} as const

export interface PeopleSelectorFilters {
  mode: keyof typeof PeopleSelectorModeLabel
  q?: string
  direction: Direction[]
}

const model = defineModel<PeopleSelectorFilters>({ required: true })
const q = ref(model.value.q)
</script>

<template>
  <v-select
    v-model="model.mode"
    label="Режим"
    :items="selectItems(PeopleSelectorModeLabel)"
    density="comfortable"
  />
  <template v-if="model.mode === 'clients'">
    <UiMultipleSelect
      v-model="model.direction"
      :items="selectItems(DirectionLabel)"
      label="Направления"
      density="comfortable"
    />
    <v-text-field
      v-model="q"
      label="Поиск"
      density="comfortable"
      @keydown.enter="model.q = (q || undefined)"
    />
  </template>
</template>

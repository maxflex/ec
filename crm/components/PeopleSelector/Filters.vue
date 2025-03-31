<script setup lang="ts">
const SendToModeLabel = {
  clients: 'клиенты',
  teachers: 'преподаватели',
} as const

export interface PeopleSelectorFilters {
  mode: keyof typeof SendToModeLabel
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
    :items="selectItems(SendToModeLabel)"
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

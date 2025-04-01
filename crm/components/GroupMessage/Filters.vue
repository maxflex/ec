<script setup lang="ts">
export interface GroupMessageFilters {
  mode: Recepient
  q?: string
  direction: Direction[]
}

const model = defineModel<GroupMessageFilters>({ required: true })
const q = ref(model.value.q)
</script>

<template>
  <v-select
    v-model="model.mode"
    label="Режим"
    :items="selectItems(RecepientLabel)"
    density="comfortable"
    class="lowercase-select-items"
    :menu-props="{
      class: 'lowercase-select-items',
    }"
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

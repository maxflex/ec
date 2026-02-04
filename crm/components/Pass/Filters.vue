<script lang="ts" setup>
export interface PassFilters {
  status?: PassStatus
  direction: Direction[]
  q?: string
}

const model = defineModel<PassFilters>({ required: true })
const q = ref(model.value.q)

function clear() {
  q.value = ''
  model.value.q = ''
}
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

  <div class="relative">
    <v-text-field
      v-model="q"
      label="Поиск"
      density="comfortable"
      @keydown.enter="model.q = (q || undefined)"
    />
    <UiUnderInput v-if="!!q" @click="clear()" />
  </div>
</template>

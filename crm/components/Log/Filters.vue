<script lang="ts" setup>
export interface Filters {
  type?: LogType
  table?: LogTable
  row_id?: string
}

const emit = defineEmits<{
  (e: 'apply', filters: Filters): void
}>()
const q = ref('')
const input = ref()
const filters = ref<Filters>({})

function onSearch() {
  input.value.blur()
  q.value = q.value.trim()
  filters.value.row_id = q.value ?? undefined
}

watch(filters.value, () => emit('apply', filters.value))
</script>

<template>
  <div>
    <UiClearableSelect
      v-model="filters.type"
      label="Действие"
      :items="selectItems(LogTypeLabel)"
      density="comfortable"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.table"
      label="Таблица"
      :items="selectItems(LogTableLabel)"
      density="comfortable"
    />
  </div>
  <div>
    <v-text-field
      ref="input"
      v-model="q"
      label="ID"
      density="comfortable"
      hide-spin-buttons
      type="number"
      @keydown.enter="onSearch"
    />
  </div>
</template>

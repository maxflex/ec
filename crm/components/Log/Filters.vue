<script lang="ts" setup>
import type { LogTable } from '.'
import { tables } from '.'

export interface LogFilters {
  type?: LogType
  table?: LogTable
  row_id?: string
  user_id?: number
  device?: LogDevice
  q?: string
}

const model = defineModel<LogFilters>({ required: true })
const rowId = ref(model.value.row_id)
const q = ref(model.value.q)

function clearRowId() {
  rowId.value = ''
  model.value.row_id = ''
}

function clear() {
  q.value = ''
  model.value.q = ''
}
</script>

<template>
  <UiClearableSelect
    v-model="model.type"
    label="Действие"
    :items="selectItems(LogTypeLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.table"
    label="Таблица"
    :items="tables"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.device"
    label="Устройство"
    :items="selectItems(LogDeviceLabel)"
    density="comfortable"
  />
  <UserSelector
    v-model="model.user_id"
    density="comfortable"
  />
  <div class="relative">
    <v-text-field
      v-model="rowId"
      label="ID"
      density="comfortable"
      hide-spin-buttons
      type="number"
      @keydown.enter="model.row_id = rowId"
    />
    <UiClear v-if="!!rowId" @click="clearRowId()" />
  </div>

  <div class="relative">
    <v-text-field
      v-model="q"
      label="Поиск"
      density="comfortable"
      @keydown.enter="model.q = (q || undefined)"
    />
    <UiClear v-if="!!q" @click="clear()" />
  </div>
</template>

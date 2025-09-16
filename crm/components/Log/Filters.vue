<script lang="ts" setup>
export interface LogFilters {
  type?: LogType
  table?: string
  row_id?: string
  user_id?: number
  device?: LogDevice
  q?: string
}

const model = defineModel<LogFilters>({ required: true })
const rowId = ref(model.value.row_id)
const q = ref(model.value.q)

/**
select group_concat(concat("'", `TABLE_NAME`, "'"))
from information_schema.TABLES
where TABLE_SCHEMA = 'ec' and `TABLE_NAME` not in ('logs', 'calls', 'migrations', 'macros', 'errors');
 */
const tables = [
  'client_complaints',
  'client_groups',
  'client_lessons',
  'client_parents',
  'client_reviews',
  'client_tests',
  'clients',
  'comments',
  'contract_payments',
  'contract_version_payments',
  'contract_version_program_prices',
  'contract_version_programs',
  'contract_versions',
  'contracts',
  'event_participants',
  'events',
  'exam_scores',
  'grades',
  'group_acts',
  'groups',
  'head_teacher_reports',
  'instruction_signs',
  'instructions',
  'lessons',
  'other_payments',
  'pass_logs',
  'passes',
  'phones',
  'photos',
  'reports',
  'requests',
  'projects',
  'sms_messages',
  'stats_presets',
  'teacher_payments',
  'teacher_services',
  'teachers',
  'telegram_lists',
  'telegram_messages',
  'tests',
  'users',
  'vacations',
  'web_review_programs',
  'web_reviews',
]
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
  <v-text-field
    v-model="rowId"
    label="ID"
    density="comfortable"
    hide-spin-buttons
    type="number"
    @keydown.enter="model.row_id = rowId"
  />
  <v-text-field
    v-model="q"
    label="Поиск"
    density="comfortable"
    @keydown.enter="model.q = (q || undefined)"
  />
</template>

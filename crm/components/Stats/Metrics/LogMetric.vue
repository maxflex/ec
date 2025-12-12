<script lang="ts">
import type { LogTable } from '~/components/Log'
import { tables } from '~/components/Log'

interface Filters {
  table: LogTable[]
  type: LogType[]
  device: LogDevice[]
  entity_type: EntityType[]
}

const filterDefaults: Filters = {
  table: [],
  device: [],
  type: [],
  entity_type: [],
}

export default {
  label: 'Логи',
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
</script>

<template>
  <div>
    <UiMultipleSelect
      v-model="filters.type"
      label="Действие"
      :items="selectItems(LogTypeLabel)"
    />
  </div>
  <div>
    <UiMultipleSelect v-model="filters.table" :items="tables" label="Таблица" />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.device"
      label="Устройство"
      :items="selectItems(LogDeviceLabel)"
    />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.entity_type"
      label="Пользователь"
      :items="selectItems(EntityTypeLabel, [
        EntityTypeValue.user,
        EntityTypeValue.client,
        EntityTypeValue.representative,
        EntityTypeValue.teacher,
      ])"
    />
  </div>
</template>

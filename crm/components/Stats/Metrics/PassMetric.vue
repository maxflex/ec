<script lang="ts">
const PassTypeFilterLabel = {
  noRequest: 'разовый без заявки',
  hasRequest: 'разовый по заявке',
  client: 'ученик',
  clientParent: 'родитель',
  teacher: 'преподаватель',
  user: 'администратор',
} as const

type PassTypeFilter = keyof typeof PassTypeFilterLabel

interface Filters {
  type: PassTypeFilter[]
  direction: Direction[]
}

const filterDefaults: Filters = {
  type: [],
  direction: [],
}

export default {
  label: 'Пропуски',
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
</script>

<template>
  <div>
    <UiMultipleSelect v-model="filters.type" :items="selectItems(PassTypeFilterLabel)" label="Вид пропуска" />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.direction"
      :items="selectItems(DirectionLabel)"
      label="Направление"
    />
  </div>
</template>

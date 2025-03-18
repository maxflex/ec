<script lang="ts">
interface Filters {
  direction: Direction[]
  has_used?: number
}

const filterDefaults: Filters = {
  direction: [],
}

export default {
  label: 'Заявки по разрешениям на пропуска',
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
      v-model="filters.direction"
      :items="selectItems(DirectionLabel)"
      label="Направление"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.has_used"
      :items="yesNo('есть хотя бы 1 использованное разрешение', 'нет использованных разрешений')"
      label="Разрешения на пропуска в заявке"
    />
  </div>
</template>

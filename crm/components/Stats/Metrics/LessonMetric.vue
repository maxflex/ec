<script lang="ts">
interface Filters {
  status: LessonStatus[]
  direction: Direction[]
  is_free?: boolean
  is_unplanned?: boolean
}

const filterDefaults: Filters = {
  status: [],
  direction: [],
}

export default {
  label: 'Уроки',
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
      v-model="filters.status"
      :items="selectItems(LessonStatusLabel)"
      label="Статус"
    />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.direction"
      :items="selectItems(DirectionLabel)"
      label="Направление"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.is_free"
      :items="yesNo('да', 'нет')"
      label="Бесплатное"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.is_unplanned"
      :items="yesNo('да', 'нет')"
      label="Внеплановое"
    />
  </div>
</template>

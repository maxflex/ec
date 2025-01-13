<script lang="ts" setup>
interface Filters {
  status?: LessonStatus
  is_free?: boolean
  is_unplanned?: boolean
  direction: Direction[]
}

const filters = ref<Filters>({
  direction: [],
})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Уроки',
  filters: {
    direction: [],
  },
}
</script>

<template>
  <div>
    <UiClearableSelect
      v-model="filters.status"
      :items="selectItems(LessonStatusLabel)"
      label="Статус урока"
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

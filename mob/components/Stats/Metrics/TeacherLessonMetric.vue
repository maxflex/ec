<script lang="ts" setup>
const { cancelled, ...LessonStatusLabelWithoutCancelled } = LessonStatusLabel

interface Filters {
  status?: LessonStatus
  direction: Direction[]
}

const filters = ref<Filters>({
  direction: [],
})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Начисления уроки препод',
  width: 150,
  filters: {
    direction: [],
  },
}
</script>

<template>
  <div>
    <UiClearableSelect
      v-model="filters.status"
      :items="selectItems(LessonStatusLabelWithoutCancelled)"
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
</template>

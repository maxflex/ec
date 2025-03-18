<script lang="ts">
interface Filters {
  direction: Direction[]
}

const filterDefaults: Filters = {
  direction: [],
}

export default {
  label: 'Начисления по урокам (ученики)',
  width: 150,
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
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

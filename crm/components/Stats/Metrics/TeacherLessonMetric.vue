<script lang="ts" setup>
const { cancelled, ...LessonStatusLabelWithoutCancelled } = LessonStatusLabel

interface Filters {
  status?: LessonStatus
}

const filters = ref<Filters>({
})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Начисления уроки препод',
  width: 130,
  filters: { },
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
</template>

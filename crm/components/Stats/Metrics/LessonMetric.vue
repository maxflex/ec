<script lang="ts" setup>
interface Filters {
  status?: LessonStatus
}

const filters = ref<Filters>({})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Уроки',
  filters: {},
}
</script>

<template>
  <div>
    <UiClearableSelect
      v-model="filters.status"
      :items="selectItems(LessonStatusLabel)"
      label="Статус занятия"
    />
  </div>
</template>

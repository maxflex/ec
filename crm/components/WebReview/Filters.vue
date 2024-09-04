<script lang="ts" setup>
export interface Filters {
  is_published?: number
  has_exam_score?: number
}

const emit = defineEmits<{
  apply: [f: Filters]
}>()

const filters = ref<Filters>({})

watch(filters.value, () => emit('apply', filters.value))
</script>

<template>
  <div>
    <UiClearableSelect
      v-model="filters.is_published"
      :items="yesNo()"
      density="comfortable"
      label="Опубликован"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.has_exam_score"
      :items="yesNo()"
      density="comfortable"
      label="Присвоен к оценке"
    />
  </div>
</template>

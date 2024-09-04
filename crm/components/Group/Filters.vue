<script lang="ts" setup>
export interface Filters {
  year: Year
  program?: Program
}

const emit = defineEmits<{
  (e: 'apply', filters: Filters): void
}>()

// const filters = ref<Filters>({})
const filters = ref<Filters>({
  year: currentAcademicYear(),
})

watch(filters.value, () => {
  emit('apply', filters.value)
})
</script>

<template>
  <div>
    <v-select
      v-model="filters.year"
      label="Учебный год"
      :items="selectItems(YearLabel)"
      density="comfortable"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.program"
      label="Программа"
      :items="selectItems(ProgramLabel)"
      density="comfortable"
    />
  </div>
</template>

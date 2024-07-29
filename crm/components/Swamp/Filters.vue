<script lang="ts" setup>
export interface Filters {
  year: Year
  program?: Program
}

const emit = defineEmits<{
  apply: [f: Filters]
}>()

const filters = ref(loadFilters<Filters>({
  year: currentAcademicYear(),
}))

watch(filters.value, () => {
  emit('apply', filters.value)
})
</script>

<template>
  <div class="filters-inputs">
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
  </div>
</template>

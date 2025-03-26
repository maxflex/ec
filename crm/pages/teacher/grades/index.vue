<script setup lang="ts">
const filters = ref<AvailableYearsFilter>({
  year: undefined,
})

const { items, indexPageData, availableYears } = useIndex<QuartersGradesResource, AvailableYearsFilter>(
  `grades`,
  filters,
  {
    loadAvailableYears: true,
  },
)

const selectedProgram = ref<Program>()
const availablePrograms = computed(() => [...new Set(items.value.map(l => l.program))])
const filteredItems = computed(() =>
  selectedProgram.value
    ? items.value.filter(l => l.program === selectedProgram.value)
    : items.value,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" @update:model-value="selectedProgram = undefined" />
      <UiClearableSelect
        v-model="selectedProgram"
        label="Программа"
        :items="
          availablePrograms.map((value) => ({
            value,
            title: ProgramLabel[value],
          }))
        "
        density="comfortable"
      />
    </template>
    <GradeList :items="filteredItems" />
  </UiIndexPage>
</template>

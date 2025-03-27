<script setup lang="ts">
const filters = useAvailableYearsFilter()

const selectedProgram = ref<Program>()

const { items, indexPageData, availableYears } = useIndex<GroupListResource>(
  `groups`,
  filters,
  {
    loadAvailableYears: true,
  },
)

const availablePrograms = computed(() => {
  return [...new Set(items.value.map(e => e.program))].map(p => ({
    value: p,
    title: ProgramLabel[p],
  }))
})

const filteredItems = computed(() => selectedProgram.value
  ? items.value.filter(e => e.program === selectedProgram.value)
  : items.value,
)

watch(filters.value, () => {
  selectedProgram.value = undefined
})
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
      <UiClearableSelect
        v-model="selectedProgram"
        label="Программа"
        :items="availablePrograms"
        density="comfortable"
      />
    </template>
    <GroupTeacherList :items="filteredItems" blur-others />
  </UiIndexPage>
</template>

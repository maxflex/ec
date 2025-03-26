<script setup lang="ts">
const filters = ref<AvailableYearsFilter>({
  year: undefined,
})

const selectedQuarter = ref<Quarter | null>(null)
const selectedProgram = ref<Program | undefined>(undefined)
const availablePrograms = ref<Program[]>([])

const { items, indexPageData, availableYears } = useIndex<JournalResource, AvailableYearsFilter>(
  `journal`,
  filters,
  {
    loadAvailableYears: true,
  },
)

const quarters = ref<{
  title: string
  value: Quarter | null
}[]>([])

watch(items, (newVal) => {
  quarters.value = [...new Set(newVal.map(e => e.lesson.quarter))].sort().map(q => ({
    value: q,
    title: q === null ? 'без четверти' : QuarterLabel[q],
  }))
  availablePrograms.value = [...new Set(newVal.map(e => e.program))]
  selectedQuarter.value = quarters.value.length ? quarters.value[0].value : null
})

const filteredItems = computed(() => items.value.filter((e) => {
  return e.lesson.quarter === selectedQuarter.value && (
    selectedProgram.value ? (e.program === selectedProgram.value) : true
  )
}))
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
      <v-select v-model="selectedQuarter" :items="quarters" label="Четверть" density="comfortable" />
      <UiClearableSelect
        v-model="selectedProgram" :items="availablePrograms.map(e => ({
          value: e,
          title: ProgramLabel[e],
        }))" density="comfortable" label="Программа"
      />
    </template>
    <JournalList :items="filteredItems" />
  </UiIndexPage>
</template>

<script setup lang="ts">
const filters = ref<YearFilters>({
  year: currentAcademicYear(),
})

const grades = ref<GradeListForClients[]>([])
const selectedQuarter = ref<Quarter | null>(null)
const selectedProgram = ref<Program | undefined>(undefined)
const availablePrograms = ref<Program[]>([])

const { items, indexPageData } = useIndex<JournalResource, YearFilters>(`journal`, filters)

const quarters = ref<{
  title: string
  value: Quarter | null
}[]>([])

watch(items, (newVal) => {
  quarters.value = [...new Set(newVal.map(e => e.lesson.quarter))].map(q => ({
    value: q,
    title: q === null ? 'без четверти' : QuarterLabel[q],
  }))
  availablePrograms.value = [...new Set(newVal.map(e => e.program))]
  selectedQuarter.value = quarters.value[0].value
  loadFinalGrades()
})

const filteredItems = computed(() => items.value.filter((e) => {
  return e.lesson.quarter === selectedQuarter.value && (
    selectedProgram.value ? (e.program === selectedProgram.value) : true
  )
}))

const filteredGrades = computed(() => selectedProgram.value ? grades.value.filter(g => g.program === selectedProgram.value) : grades.value)

async function loadFinalGrades() {
  const { data } = await useHttp<GradeListForClients[]>(
    `grades/journal`,
    {
      params: {
        year: filters.value.year,
        quarter: selectedQuarter.value,
      },
    },
  )
  grades.value = data.value!
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiYearSelector v-model="filters.year" density="comfortable" />
      <v-select v-model="selectedQuarter" :items="quarters" label="Четверть" density="comfortable" />
      <UiClearableSelect
        v-model="selectedProgram" :items="availablePrograms.map(e => ({
          value: e,
          title: ProgramLabel[e],
        }))" density="comfortable" label="Программа"
      />
    </template>
    <JournalList :items="filteredItems" />
    <GradeListForClients :items="filteredGrades" />
  </UiIndexPage>
</template>

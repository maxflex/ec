<script setup lang="ts">
const filters = useAvailableYearsFilter()

const selectedQuarter = ref<Quarter | -1>(-1)
const selectedProgram = ref<Program | undefined>(undefined)
const availablePrograms = ref<Program[]>([])

const { items, indexPageData, availableYears } = useIndex<JournalResource>(
  `journal`,
  filters,
  {
    loadAvailableYears: true,
  },
)

const quarters = ref<{
  title: string
  value: Quarter | -1
}[]>([])

watch(items, (newVal) => {
  quarters.value = [...new Set(newVal.map(e => e.lesson.quarter))].sort().map(q => ({
    value: q === null ? -1 : q,
    title: q === null ? 'без четверти' : QuarterLabel[q],
  }))
  availablePrograms.value = [...new Set(newVal.map(e => e.program))]
  selectedQuarter.value = quarters.value.length ? quarters.value[0].value : -1
})

watch(selectedProgram, () => smoothScroll('main', 'top', 'instant'))

const filteredItems = computed(() => items.value.filter((e) => {
  return (e.lesson.quarter ?? -1) === selectedQuarter.value && (
    selectedProgram.value ? (e.program === selectedProgram.value) : true
  )
}))
</script>

<template>
  <UiPageTitle>
    Дневник
  </UiPageTitle>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
      <UiChipSelector v-if="quarters.length > 1" v-model="selectedQuarter" :items="quarters" label="без четверти" />
      <UiChipSelector
        v-model="selectedProgram"
        clearable
        :items="availablePrograms.map(e => ({
          value: e,
          title: ProgramLabel[e],
        }))"
        label="программа"
      />
    </template>
    <JournalList :items="filteredItems" />
  </UiIndexPage>
</template>

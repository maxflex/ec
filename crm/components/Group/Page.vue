<script setup lang="ts">
const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const selectedProgram = ref<Program>()

const { items, indexPageData } = useIndex<GroupListResource, YearFilters>(`groups`, filters)

watch(filters.value, () => (selectedProgram.value = undefined))

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
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
      <UiClearableSelect
        v-model="selectedProgram"
        label="Программа"
        :items="availablePrograms"
        density="comfortable"
      />
    </template>
    <div class="table table--padding">
      <GroupList :items="filteredItems" />
    </div>
  </UiIndexPage>
</template>

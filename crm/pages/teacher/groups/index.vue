<script setup lang="ts">
import type { Filters } from '~/components/Group/Filters.vue'

const filters = ref<Filters>({
  year: currentAcademicYear(),
})

const selectedProgram = ref<Program>()

const { items, loading, onFiltersApply } = useIndex<GroupListResource, Filters>(`groups`, {
  defaultFilters: filters.value,
})

watch(filters.value, (filters) => {
  selectedProgram.value = undefined
  onFiltersApply(filters)
})

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
  <div class="filters">
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
          v-model="selectedProgram"
          label="Программа"
          :items="availablePrograms"
          density="comfortable"
        />
      </div>
    </div>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <div class="table table--padding">
      <GroupList :items="filteredItems" />
    </div>
  </div>
</template>

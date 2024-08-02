<script lang="ts" setup>
const emit = defineEmits<{
  apply: [f: Filters]
}>()

const swampFilterStatusLabel = {
  toFullfill: 'к исполнению',
  closedInGroup: 'в группе с закрытым договором',
  noContractInGroup: 'в группе без договора',
} as const

export interface Filters {
  year: Year
  program?: Program
  status?: keyof typeof swampFilterStatusLabel
}

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
    <div>
      <UiClearableSelect
        v-model="filters.status"
        label="Статус"
        :items="selectItems(swampFilterStatusLabel)"
        density="comfortable"
      />
    </div>
  </div>
</template>

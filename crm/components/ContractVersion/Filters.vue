<script lang="ts" setup>
const emit = defineEmits<{
  (e: 'apply', filters: Filters): void
}>()

const VersionFilterLabel = {
  first: 'первая',
  last: 'последняя',
}

export interface Filters {
  year: Year
  company?: Company
  version?: keyof typeof VersionFilterLabel
}
const filters = ref<Filters>({ year: currentAcademicYear() })

watch(filters.value, () => emit('apply', filters.value))
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
      v-model="filters.company"
      :items="selectItems(CompanyLabel)"
      density="comfortable"
      label="Тип"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.version"
      :items="selectItems(VersionFilterLabel)"
      density="comfortable"
      label="Версия"
    />
  </div>
</template>

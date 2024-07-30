<script lang="ts" setup>
export interface Filters {
  year: Year
  program?: Program
  group_id?: boolean
  is_closed?: boolean
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
    <div>
      <UiClearableSelect
        v-model="filters.group_id"
        label="В группе"
        :items="yesNo()"
        density="comfortable"
      />
    </div>
    <div>
      <UiClearableSelect
        v-model="filters.is_closed"
        label="Расторгнут"
        :items="yesNo()"
        density="comfortable"
      />
    </div>
  </div>
</template>

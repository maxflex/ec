<script lang="ts" setup>
export interface Filters {
  mode: StatsMode
  year?: Year
}
const emit = defineEmits<{ (e: 'apply', filters: Filters): void }>()
const filters = ref(loadFilters<Filters>({
  mode: 'day',
}))

watch(filters.value, () => {
  saveFilters(filters.value)
  emit('apply', filters.value)
})
</script>

<template>
  <div class="filters-inputs">
    <div>
      <v-select
        v-model="filters.mode"
        label="Отображать"
        :items="selectItems(StatsModeLabel)"
        density="comfortable"
      />
    </div>
    <div>
      <UiClearableSelect
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Учебный год"
        density="comfortable"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
export interface Filters {
  year: Year
  is_topic_verified?: boolean
}

const emit = defineEmits<{
  (e: 'apply', filters: Filters): void
}>()

// const filters = ref<Filters>({})
const filters = ref(loadFilters<Filters>({
  year: currentAcademicYear(),
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
        v-model="filters.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
    </div>
    <div>
      <UiClearableSelect
        v-model="filters.is_topic_verified"
        :items="yesNo('промодерировано', 'не промодерировано')"
        label="Модерация"
        density="comfortable"
      />
    </div>
  </div>
</template>

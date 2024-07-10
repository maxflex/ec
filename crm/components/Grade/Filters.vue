<script lang="ts" setup>
export interface Filters {
  year: Year
  program?: Program
  quarter?: Quarter
  type?: number
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
        v-model="filters.quarter"
        label="Четверть"
        :items="selectItems(QuarterLabel)"
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
        v-model="filters.type"
        label="Тип"
        :items="yesNo('созданные', 'требуется оценка')"
        density="comfortable"
      />
    </div>
    <!-- <div>
      <UiClearableSelect
        v-model="filters.is_moderated"
        label="Модерация"
        :items="yesNo('промодерирован', 'не промодерирован')"
        density="comfortable"
      />
    </div> -->
  </div>
</template>

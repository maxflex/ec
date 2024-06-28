<script lang="ts" setup>
export interface Filters {
  year: Year
  program?: Program
  is_moderated?: number
  is_published?: number
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
        v-model="filters.type"
        label="Тип"
        :items="yesNo('созданные', 'требуется отчёт')"
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
        v-model="filters.is_published"
        label="Публикация"
        :items="yesNo('опубликован', 'не опубликован')"
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

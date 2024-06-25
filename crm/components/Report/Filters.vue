<script lang="ts" setup>
export interface Filters {
  program?: Program
  year?: Year
  is_moderated?: number
  is_published?: number
}

const emit = defineEmits<{
  (e: 'apply', filters: Filters): void
}>()

// const filters = ref<Filters>({})
const filters = ref(loadFilters<Filters>({}))

watch(filters.value, () => {
  saveFilters(filters.value)
  emit('apply', filters.value)
})
</script>

<template>
  <div class="filters-inputs">
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
        v-model="filters.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
    </div>
    <div>
      <UiClearableSelect
        v-model="filters.is_published"
        label="Публикация"
        :items="selectItems({
          1: 'опубликован',
          0: 'не опубликован',
        })"
        density="comfortable"
      />
    </div>
    <div>
      <UiClearableSelect
        v-model="filters.is_moderated"
        label="Модерация"
        :items="selectItems({
          1: 'промодерирован',
          0: 'не промодерирован',
        })"
        density="comfortable"
      />
    </div>
  </div>
</template>

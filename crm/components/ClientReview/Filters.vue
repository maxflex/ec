<script lang="ts" setup>
export interface Filters {
  type?: number
  program?: Program
  teacher_id?: number
  rating?: number
}
// const count = computed(
//   () => Object.values(filters.value).filter(e => e !== undefined).length,
// )

const emit = defineEmits<{
  (e: 'apply', filters: Filters): void
}>()

const filters = ref<Filters>({})

watch(filters.value, () => emit('apply', filters.value))
</script>

<template>
  <div class="filters-inputs">
    <div>
      <UiClearableSelect
        v-model="filters.type"
        label="Тип"
        :items="yesNo('созданные', 'требуется создание')"
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
        v-model="filters.rating"
        label="Оценка"
        density="comfortable"
        :items="[5, 4, 3, 2, 1].map(e => ({
          value: e,
          title: `${e}`,
        }))"
      />
    </div>
    <div>
      <TeacherSelector
        v-model="filters.teacher_id"
        label="Преподаватель"
        density="comfortable"
      />
    </div>
  </div>
</template>

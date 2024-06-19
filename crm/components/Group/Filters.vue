<script lang="ts" setup>
// const count = computed(
//   () => Object.values(filters.value).filter(e => e !== undefined).length,
// )

const emit = defineEmits<{
  (e: 'apply', filters: GroupFilters): void
}>()

// const filters = ref<GroupFilters>({})
const filters = ref(loadFilters<GroupFilters>({}))

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
  </div>
</template>

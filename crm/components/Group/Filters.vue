<script lang="ts" setup>
const filters = ref<GroupFilters>({})

watch(filters.value, () => emit('apply', filters.value))

// const count = computed(
//   () => Object.values(filters.value).filter(e => e !== undefined).length,
// )

const emit = defineEmits<{
  (e: 'apply', filters: GroupFilters): void
}>()
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
        :items="selectItems(YearLabel, true)"
        density="comfortable"
      />
    </div>
  </div>
</template>

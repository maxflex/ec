<script lang="ts" setup>
const filters = ref<RequestFilters>({})

watch(filters.value, () => emit('apply', filters.value))

// const count = computed(
//   () => Object.values(filters.value).filter(e => e !== undefined).length,
// )

const emit = defineEmits<{
  (e: 'apply', filters: RequestFilters): void
}>()
</script>

<template>
  <div class="filters-inputs">
    <div>
      <UiClearableSelect
        v-model="filters.status"
        label="Статус"
        :items="selectItems(RequestStatusLabel)"
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
  </div>
</template>

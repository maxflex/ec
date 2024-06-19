<script lang="ts" setup>
// const count = computed(
//   () => Object.values(filters.value).filter(e => e !== undefined).length,
// )

const emit = defineEmits<{
  (e: 'apply', filters: RequestFilters): void
}>()

const filters = ref<RequestFilters>({})

watch(filters.value, () => emit('apply', filters.value))
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

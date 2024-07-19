<script lang="ts" setup>
export interface Filters {
  type?: number
}

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
        :items="yesNo('групповые', 'личные')"
        density="comfortable"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
export interface Filters {
  signed?: number
}

const emit = defineEmits<{
  apply: [f: Filters]
}>()

const filters = ref<Filters>({})

watch(filters.value, () => emit('apply', filters.value))
</script>

<template>
  <div>
    <UiClearableSelect
      v-model="filters.signed"
      label="Статус"
      :items="yesNo('подписанные', 'неподписанные')"
      density="comfortable"
    />
  </div>
</template>

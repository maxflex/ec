<script lang="ts" setup>
export interface Filters {
  q?: string
  year?: Year
}
const emit = defineEmits<{ (e: 'apply', filters: Filters): void }>()
const filters = ref<Filters>({})
const q = ref('')
const input = ref()

watch(filters.value, () => {
  emit('apply', filters.value)
})

function onSearch() {
  input.value.blur()
  q.value = q.value.trim()
  filters.value.q = q.value ?? undefined
}
</script>

<template>
  <div class="filters-inputs">
    <div>
      <v-text-field
        ref="input"
        v-model="q"
        label="Имя"
        density="comfortable"
        @keydown.enter="onSearch"
      />
    </div>
    <div>
      <UiClearableSelect
        v-model="filters.year"
        label="Договоры"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
    </div>
  </div>
</template>

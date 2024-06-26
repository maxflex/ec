<script lang="ts" setup>
export interface Filters {
  q?: string
  is_active?: number
}

const emit = defineEmits<{
  (e: 'apply', filters: Filters): void
}>()
const q = ref('')
const input = ref()
const filters = ref<Filters>({})

function onSearch() {
  input.value.blur()
  q.value = q.value.trim()
  filters.value.q = q.value ?? undefined
}

watch(filters.value, () => emit('apply', filters.value))
</script>

<template>
  <div class="filters-inputs">
    <div>
      <UiClearableSelect
        v-model="filters.is_active"
        label="Статус"
        :items="selectItems(UserStatusLabel)"
        density="comfortable"
      />
    </div>
    <div>
      <v-text-field
        ref="input"
        v-model="q"
        label="Имя"
        density="comfortable"
        @keydown.enter="onSearch"
      />
    </div>
  </div>
</template>

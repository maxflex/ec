<script lang="ts" setup>
export interface Filters {
  q?: string
  status?: TeacherStatus
  subjects: Subject[]
}
const emit = defineEmits<{ (e: 'apply', filters: Filters): void }>()
const filters = ref<Filters>({ subjects: [] })
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
        v-model="filters.status"
        label="Статус"
        :items="selectItems(TeacherStatusLabel)"
        density="comfortable"
      />
    </div>
    <div>
      <v-select
        v-model="filters.subjects"
        label="Предметы"
        :items="selectItems(SubjectLabel)"
        density="comfortable"
        multiple
        :menu-props="{
          maxHeight: 999,
        }"
      />
    </div>
  </div>
</template>

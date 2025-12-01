<script lang="ts" setup>
export interface TeacherFilters {
  q?: string
  status?: TeacherStatus
  subjects: Subject[]
}

const model = defineModel<TeacherFilters>({ required: true })
const q = ref(model.value.q)

function clear() {
  q.value = ''
  model.value.q = ''
}
</script>

<template>
  <div class="relative">
    <v-text-field
      v-model="q"
      label="Имя"
      density="comfortable"
      @keydown.enter="model.q = q"
    />
    <UiClear v-if="!!q" @click="clear()" />
  </div>

  <UiClearableSelect
    v-model="model.status"
    label="Статус"
    :items="selectItems(TeacherStatusLabel)"
    density="comfortable"
  />
  <v-select
    v-model="model.subjects"
    label="Предметы"
    :items="selectItems(SubjectLabel)"
    density="comfortable"
    multiple
    :menu-props="{
      maxHeight: 999,
    }"
  />
</template>

<script lang="ts" setup>
const model = defineModel<TeacherFilters>({ required: true })
const q = ref(model.value.q)
</script>

<template>
  <v-text-field
    v-model="q"
    label="Имя"
    density="comfortable"
    @keydown.enter="model.q = q"
  />
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

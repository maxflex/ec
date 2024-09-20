<script lang="ts" setup>
const model = defineModel<TeacherFilters>({ required: true })
const q = ref(model.value.q)
</script>

<template>
  <div>
    <v-text-field
      ref="input"
      v-model="q"
      label="Имя"
      density="comfortable"
      @keydown.enter="model.q = q"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="model.status"
      label="Статус"
      :items="selectItems(TeacherStatusLabel)"
      density="comfortable"
    />
  </div>
  <div>
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
  </div>
</template>

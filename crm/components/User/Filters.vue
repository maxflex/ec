<script lang="ts" setup>
const model = defineModel<UserFilters>({ required: true })
const q = ref(model.value.q)
</script>

<template>
  <v-text-field
    ref="input"
    v-model="q"
    label="Имя"
    density="comfortable"
    @keydown.enter="model.q = q"
  />
  <UiClearableSelect
    v-model="model.is_active"
    label="Статус"
    :items="selectItems(UserStatusLabel)"
    density="comfortable"
  />
</template>

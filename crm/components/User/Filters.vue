<script lang="ts" setup>
export interface UserFilters {
  q?: string
  is_active?: number
}

const model = defineModel<UserFilters>({ required: true })
const q = ref(model.value.q)
</script>

<template>
  <UiClearableSelect
    v-model="model.is_active"
    label="Статус"
    :items="selectItems(UserStatusLabel)"
    density="comfortable"
  />
  <v-text-field
    v-model="q"
    label="Имя"
    density="comfortable"
    @keydown.enter="model.q = q"
  />
</template>

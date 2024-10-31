<script setup lang="ts">
const { label = 'Преподаватель' } = defineProps<{ label: string }>()
const model = defineModel<number | null>()
const teachers = useTeachers()
</script>

<template>
  <UiClearableSelect
    v-model="model"
    v-bind="$attrs"
    :items="teachers"
    :item-title="formatFullName"
    item-value="id"
    :loading="teachers === undefined"
    :label="label"
  >
    <template #item="{ props, item }">
      <v-list-item v-bind="props" :class="{ 'text-gray': item.raw.status !== 'active' }">
        <template #prepend />
        <template #title>
          {{ item.title }}
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>
</template>

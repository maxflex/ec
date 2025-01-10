<script setup lang="ts">
const { multiple } = defineProps<{
  multiple?: boolean
}>()

const model = defineModel<number | number[] | null>()
const users = useUsers()
</script>

<template>
  <UiMultipleSelect
    v-if="multiple"
    v-model="model"
    :items="users"
    :item-title="formatName"
    item-value="id"
  />
  <UiClearableSelect
    v-else
    v-model="model"
    v-bind="$attrs"
    :items="users"
    :item-title="formatName"
    item-value="id"
    :loading="users === undefined"
  >
    <template #item="{ props, item }">
      <v-list-item v-bind="props" :class="{ 'text-gray': !item.raw.is_active }">
        <template #prepend />
        <template #title>
          {{ item.title }}
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>
</template>

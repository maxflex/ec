<script setup lang="ts">
const { label = 'Кабинет' } = defineProps<{
  label?: string
  items: Array<{
    cabinet: Cabinet
    is_busy: boolean
  }>
}>()
const model = defineModel<Cabinet | null >()
</script>

<template>
  <UiClearableSelect
    v-model="model"
    v-bind="$attrs"
    :items="items"
    item-value="cabinet"
    :item-title="({ cabinet }) => CabinetLabel[cabinet]"
    :label="label"
  >
    <template #item="{ props, item }">
      <v-list-item v-bind="props" :class="{ 'text-gray': item.raw.is_busy }">
        <template #prepend />
        <template #title>
          {{ item.title }}
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>
</template>

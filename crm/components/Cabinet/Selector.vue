<script setup lang="ts">
import { orderBy } from 'lodash-es'
import { Cabinets } from '.'

const items: SelectItems = orderBy(Object.keys(Cabinets).map(c => ({
  value: c,
  title: Cabinets[c].label,
})), 'title')

const model = defineModel<string | null>()
</script>

<template>
  <UiClearableSelect
    v-model="model"
    v-bind="$attrs"
    :items="items"
    nullify
  >
    <template v-if="model" #selection="{ item }">
      <CabinetWithCapacity :item="item.value" />
    </template>
    <template #item="{ props, item }">
      <v-list-item v-bind="props" :class="{ 'text-gray': Cabinets[item.value].capacity === 0 }">
        <template #prepend />
        <template #title>
          <CabinetWithCapacity :item="item.value" />
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>
</template>

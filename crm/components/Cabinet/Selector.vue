<script setup lang="ts">
import { Cabinets } from '.'

const items = Object.keys(Cabinets).map(c => ({
  value: c,
  title: Cabinets[c].label,
}))

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
      <v-list-item v-bind="props">
        <template #prepend />
        <template #title>
          <CabinetWithCapacity :item="item.value" />
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>
</template>

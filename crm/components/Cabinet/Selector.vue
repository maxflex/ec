<script setup lang="ts">
import { Cabinets } from '.'

interface BusyCabinet {
  cabinet: string
  is_busy: boolean
}

const modelDefaults: BusyCabinet[] = Object.keys(Cabinets)
  .filter(c => Cabinets[c].capacity > 0)
  .map(c => ({
    cabinet: c,
    is_busy: false,
  }))

const items = ref<BusyCabinet[]>(modelDefaults)

const model = defineModel<string | null>()
</script>

<template>
  <UiClearableSelect
    v-model="model"
    v-bind="$attrs"
    :items="items"
    item-value="cabinet"
  >
    <template #selection="{ item }">
      {{ Cabinets[item.value].label }}<span v-if="Cabinets[item.value].capacity">-{{ Cabinets[item.value].capacity }}</span>
    </template>
    <template #item="{ props, item }">
      <v-list-item
        v-bind="props"
        :class="{ 'text-gray': item.raw.is_busy }"
      >
        <template #prepend />
        <template #title>
          <span style="width: 50px; display: inline-block;">
            {{ Cabinets[item.value].label }}<span v-if="Cabinets[item.value].capacity">-{{ Cabinets[item.value].capacity }}</span>
          </span>
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>
</template>

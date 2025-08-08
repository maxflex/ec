<script setup lang="ts">
import type { Density } from 'vuetify/lib/composables/density.mjs'

const { disabled, density = 'comfortable' } = defineProps<{
  disabled?: boolean
  density?: Density
}>()

const model = defineModel<Program | Program[]>({ required: true })

function clear() {
  model.value = []
}
</script>

<template>
  <v-select
    v-model="model"
    class="program-selector"
    :items="selectItems(ProgramLabel)"
    label="Программа"
    variant="outlined"
    :density="density"
    hide-details
    :disabled="disabled"
    no-auto-scroll
  >
    <template v-if="Array.isArray(model) && model.length > 1" #selection="{ index }">
      <template v-if="index === 0">
        выбрано: {{ model.length }}
      </template>
    </template>
    <template #no-data>
      <v-list-item>
        ничего не найдено
      </v-list-item>
    </template>
    <template v-if="!disabled && Array.isArray(model) && model.length" #append>
      <a @click="clear()">очистить</a>
    </template>
  </v-select>
</template>

<style lang="scss">
.program-selector {
  .v-input__append {
    position: absolute;
    bottom: 0;
    font-size: 12px;
    cursor: pointer;
  }
}
</style>

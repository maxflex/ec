<script setup lang="ts">
import type { Filters } from '~/components/Instruction/Filters.vue'
import type { InstructionDialog } from '#build/components'

const instructionDialog = ref<InstanceType<typeof InstructionDialog>>()
const { items, loading, onFiltersApply } = useIndex<InstructionListResource, Filters>(`instructions`)

function onCreated(item: InstructionListResource) {
  items.value.unshift(item)
  itemUpdated('instruction', item.id)
}
</script>

<template>
  <div class="filters">
    <InstructionFilters @apply="onFiltersApply" />
    <div />
    <v-btn
      append-icon="$next"
      color="primary"
      @click="instructionDialog?.create()"
    >
      добавить инструкцию
    </v-btn>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <InstructionList :items="items" />
  </div>
  <InstructionDialog ref="instructionDialog" @created="onCreated" />
</template>

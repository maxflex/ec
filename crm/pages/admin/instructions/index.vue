<script setup lang="ts">
import type { Filters } from '~/components/Instruction/Filters.vue'

const { items, loading } = useIndex<InstructionListResource, Filters>(`instructions`)
</script>

<template>
  <div class="filters">
    <!-- <InstructionFilters @apply="onFiltersApply" /> -->
    <div />
    <v-btn
      append-icon="$next"
      color="primary"
      :ripple="false"
      :to="{
        name: 'instructions-create',
      }"
    >
      добавить инструкцию
    </v-btn>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <InstructionList :items="items" />
  </div>
</template>

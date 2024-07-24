<script setup lang="ts">
import type { Filters } from '~/components/Instruction/Filters.vue'

const { items, loading, onFiltersApply } = useIndex<InstructionTeacherResource, Filters>(
  `instructions`,
)
</script>

<template>
  <div class="filters">
    <InstructionFilters @apply="onFiltersApply" />
    <div />
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <InstructionTeacherList :items="items" />
  </div>
</template>

<script setup lang="ts">
import type { ScheduleDraftGroup } from '.'

const { item, contractId } = defineProps<{
  item: ScheduleDraftGroup
  contractId: number
}>()

const hasProblems = (function () {
  if (item.overlap?.count) {
    return true
  }
  if (item.uncunducted_count) {
    return true
  }
  return false
})()
</script>

<template>
  <v-tooltip location="bottom">
    <template #activator="{ props }">
      <v-chip
        v-if="hasProblems" label color="error" density="comfortable" v-bind="props"
        class="cursor-default schedule-draft-problems"
      >
        есть проблемы
      </v-chip>
    </template>
    <div v-if="item.overlap?.count">
      {{ item.overlap!.count }} пересечений
      ({{ item.overlap!.programs.map(e => ProgramShortLabel[e]).join(', ') }})
    </div>
    <div v-if="item.uncunducted_count">
      {{ item.uncunducted_count }} непроведенных занятий
    </div>
  </v-tooltip>
</template>

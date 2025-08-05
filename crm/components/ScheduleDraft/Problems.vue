<script setup lang="ts">
import type { ScheduleDraftGroup, ScheduleDraftStudent } from '.'

const { item, contractId } = defineProps<{
  item: ScheduleDraftGroup | ScheduleDraftStudent
  contractId: number
}>()

const hasOverlap = !!item.overlap?.count
const hasUnconducted = item.uncunducted_count > 0
const hasProcessInAnotherContract = item.swamp && item.current_contract_id && item.current_contract_id !== contractId

const hasProblems = hasOverlap || hasUnconducted || hasProcessInAnotherContract
</script>

<template>
  <v-tooltip v-if="hasProblems" location="bottom">
    <template #activator="{ props }">
      <v-chip label color="error" density="comfortable" v-bind="props" class="cursor-default schedule-draft-problems">
        есть проблемы
      </v-chip>
    </template>
    <div v-if="hasProcessInAnotherContract">
      добавлен по договору №{{ item.current_contract_id }}
    </div>
    <div v-if="hasOverlap">
      {{ item.overlap!.count }} пересечений
      ({{ item.overlap!.programs.map(e => ProgramShortLabel[e]).join(', ') }})
    </div>
    <div v-if="hasUnconducted">
      {{ item.uncunducted_count }} непроведенных занятий
    </div>
  </v-tooltip>
</template>

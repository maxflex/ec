<script setup lang="ts">
import type { ScheduleDraftGroup } from '.'
import { mdiAlertBox } from '@mdi/js'

const { item, contractId } = defineProps<{
  item: ScheduleDraftGroup
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
      <v-icon :icon="mdiAlertBox" v-bind="props" color="error" :size="26" />
      <!-- <v-chip label color="error" density="comfortable" v-bind="props" class="cursor-default schedule-draft-problems">
        проблемы
      </v-chip> -->
    </template>
    <div v-if="hasProcessInAnotherContract">
      добавлен в эту группу по другому договору – №{{ item.current_contract_id }}
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

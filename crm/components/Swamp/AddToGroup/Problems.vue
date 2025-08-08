<script setup lang="ts">
import type { AddToGroupItem } from '.'
import { mdiAlertBox } from '@mdi/js'

const { item, groupId } = defineProps<{
  item: AddToGroupItem
  groupId: number
}>()

const hasOverlap = !!item.overlap?.count
const hasUnconducted = item.uncunducted_count > 0
// добавлен в эту группу по этой программе
const isAlreadyInThisGroup = item.group_id === groupId

// добавлен в другую группу
const isInAnotherGroup = item.group_id && !isAlreadyInThisGroup

// не добален по этой программе, но есть другая прогармма, по которой добавлен
const isInAnotherContract = item.current_contract_id !== item.contract_id
</script>

<template>
  <v-tooltip location="bottom">
    <template #activator="{ props }">
      <v-icon :icon="mdiAlertBox" v-bind="props" color="error" :size="26" />
    </template>
    <div v-if="isInAnotherGroup">
      добавлен в ГР-{{ item.group_id }} по этой программе
    </div>
    <div v-else-if="isInAnotherContract">
      добавлен по другому договору №{{ item.current_contract_id }}
    </div>
    <div v-else-if="isAlreadyInThisGroup">
      добавлен в эту группу по договору №{{ item.current_contract_id }}
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

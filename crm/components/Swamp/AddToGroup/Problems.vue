<script setup lang="ts">
import type { AddToGroupItem } from '.'
import { mdiAlertBox } from '@mdi/js'

const { item, groupId } = defineProps<{
  item: AddToGroupItem
  groupId: number
}>()

const hasOverlap = !!item.overlap?.count
const hasUnconducted = item.uncunducted_count > 0
const isAlreadyAdded = !!item.group_id

const hasProblems = hasOverlap || hasUnconducted || isAlreadyAdded
</script>

<template>
  <v-tooltip v-if="hasProblems" location="bottom">
    <template #activator="{ props }">
      <v-icon :icon="mdiAlertBox" v-bind="props" color="error" :size="26" />
    </template>
    <div v-if="isAlreadyAdded">
      <template v-if="groupId === item.group_id">
        добавлен в эту группу
      </template>
      <template v-else>
        добавлен в другую группу
      </template>
      по договору №{{ item.current_contract_id }}
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

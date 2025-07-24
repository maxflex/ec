<script setup lang="ts">
import type { ScheduleDraftGroup } from '.'
import { mdiArrowRightThin } from '@mdi/js'

const { items, contractId } = defineProps<{
  items: ScheduleDraftGroup[]
  contractId: number | null
}>()

const emit = defineEmits<{
  addToGroup: [e: ScheduleDraftGroup]
  removeFromGroup: [e: ScheduleDraftGroup]
  jumpToContract: [e: ScheduleDraftGroup]
}>()

function getElementId(groupId: number, cId: number | null | undefined) {
  return `schedule-draft-group-${groupId}${cId ? `-${cId}` : ''}`
}

function isChanged(g: ScheduleDraftGroup): boolean {
  if (g.swamp) {
    if (g.current_contract_id && g.current_contract_id !== g.original_contract_id) {
      return true
    }
    if (g.original_contract_id && g.original_contract_id !== g.current_contract_id && g.original_contract_id !== contractId) {
      return true
    }
  }
  else {
    if (g.original_contract_id && g.original_contract_id === contractId) {
      return true
    }
  }
  return false
}
</script>

<template>
  <v-table class="table-padding schedule-draft-group">
    <tbody>
      <tr
        v-for="item in items"
        :id="getElementId(item.id, contractId)"
        :key="item.id"
        :class="{ changed: isChanged(item) }"
      >
        <td width="100">
          <NuxtLink :to="{ name: 'groups-id', params: { id: item.id } }">
            ГР-{{ item.id }}
          </NuxtLink>
        </td>
        <td width="190">
          <GroupTeachers :item="item" />
        </td>
        <td width="150">
          {{ ProgramShortLabel[item.program] }}
        </td>
        <td width="140">
          <UiIfSet :value="item.lesson_counts.conducted || item.lesson_counts.planned">
            <template #empty>
              занятий нет
            </template>
            <GroupLessonCounts :item="item" />
          </UiIfSet>
        </td>
        <td width="70">
          <UiIfSet :value="!!item.client_groups_count">
            <template #empty>
              0 уч.
            </template>
            {{ item.client_groups_count }} уч.
          </UiIfSet>
        </td>
        <td width="140">
          <UiIfSet :value="Object.keys(item.teeth).length > 0">
            <template #empty>
              расписание отсутствует
            </template>
            <TeethAsText :items="item.teeth" />
          </UiIfSet>
        </td>
        <td v-if="item.swamp && item.current_contract_id !== contractId" class="text-gray">
          <div v-if="item.overlap?.count" class="text-error">
            {{ item.overlap!.count }} пересечений
            ({{ item.overlap!.programs.map(e => ProgramShortLabel[e]).join(', ') }})
          </div>
          <div v-if="item.uncunducted_count" class="text-error">
            {{ item.uncunducted_count }} непроведенных занятий
          </div>
          <span v-if="item.original_contract_id !== item.current_contract_id" class="text-error">
            <span v-if="item.original_contract_id === contractId">
              убран в черновике
            </span>
            <span>
              добавлен в черновике по договору №{{ item.current_contract_id }}
            </span>
          </span>
          <span v-else class="text-error">
            добавлен по договору №{{ item.current_contract_id }}
          </span>
        </td>
        <!-- есть процесс по договору -->
        <td v-else-if="item.swamp">
          <!-- но в оригинале не было -->
          <!-- в "проект по договору" не показываем -->
          <template v-if="item.swamp.id > 0">
            {{ item.swamp.lessons_conducted }}
            <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
            {{ item.swamp.total_lessons }}
          </template>
          <div>
            {{ SwampStatusLabel[item.swamp.status] }}
          </div>
          <ScheduleDraftProblems :item="item" :contract-id="contractId" />
          <div class="table-actionss">
            <v-btn color="error" density="comfortable" icon="$minus" :size="48" @click="emit('removeFromGroup', item)" />
          </div>
        </td>
        <!-- нет процесса по договору -->
        <td v-else>
          <ScheduleDraftProblems :item="item" :contract-id="contractId" />
          <!-- <div v-if="item.overlap?.count" class="text-error">
            {{ item.overlap!.count }} пересечений
            ({{ item.overlap!.programs.map(e => ProgramShortLabel[e]).join(', ') }})
          </div>
          <div v-if="item.uncunducted_count" class="text-error">
            {{ item.uncunducted_count }} непроведенных занятий
          </div> -->
          <div class="table-actionss">
            <v-btn color="primary" density="comfortable" icon="$plus" :size="48" @click="emit('addToGroup', item)" />
          </div>
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.schedule-draft-group {
  td {
    box-sizing: content-box;
    position: relative;
    transition: none !important;
    &:last-child {
      .schedule-draft-problems:not(:first-child) {
        margin-top: 20px !important;
      }
    }
  }

  .table-actionss {
    width: 70px !important;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 !important;
  }

  .v-table__wrapper {
    overflow: hidden !important;
  }

  tr:last-child td {
    border-bottom: 1px solid rgb(var(--v-theme-border));
  }
}
</style>

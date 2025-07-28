<script setup lang="ts">
import type { ScheduleDraftGroup } from '.'
import { mdiArrowRightThin } from '@mdi/js'
import { isGroupChangedInContract } from '.'

const { items, contractId } = defineProps<{
  items: ScheduleDraftGroup[]
  contractId: number
}>()

const emit = defineEmits<{
  addToGroup: [e: ScheduleDraftGroup]
  removeFromGroup: [e: ScheduleDraftGroup]
  jumpToContract: [e: ScheduleDraftGroup]
}>()

function getElementId(groupId: number, cId: number | null | undefined) {
  return `schedule-draft-group-${groupId}${cId ? `-${cId}` : ''}`
}
</script>

<template>
  <v-table class="table-padding schedule-draft-group">
    <tbody>
      <tr
        v-for="item in items"
        :id="getElementId(item.id, contractId)"
        :key="item.id"
        :class="{ changed: isGroupChangedInContract(item, contractId!) }"
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
        <td>
          <!-- Группа сейчас находится в ДРУГОМ договоре (нет действий, нет процесса) -->
          <template v-if="item.swamp && item.current_contract_id !== contractId">
            <ScheduleDraftProblems :item="item" :contract-id="contractId" />
          </template>

          <!-- Группа находится в ЭТОМ договоре (есть действия, есть процесс по по договору) -->
          <template v-else>
            <!-- есть процесс по договору -->
            <template v-if="item.swamp">
              <!-- (id > 0) в "новых программах" не показываем -->
              <div v-if="item.swamp.id > 0">
                {{ item.swamp.lessons_conducted }}
                <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
                {{ item.swamp.total_lessons }}
              </div>
              <div>
                {{ SwampStatusLabel[item.swamp.status] }}
              </div>
            </template>

            <ScheduleDraftProblems :item="item" :contract-id="contractId" />

            <!-- действия -->
            <div class="table-actionss">
              <v-btn v-if="item.swamp" color="error" density="comfortable" icon="$minus" :size="48" @click="emit('removeFromGroup', item)" />
              <v-btn v-else color="primary" density="comfortable" icon="$plus" :size="48" @click="emit('addToGroup', item)" />
            </div>
          </template>
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

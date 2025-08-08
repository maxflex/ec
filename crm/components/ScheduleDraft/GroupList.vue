<script setup lang="ts">
import type { ScheduleDraftGroup } from '.'
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

const isAlreadyInOtherGroup = computed(() => items.some(e => e.swamp && e.current_contract_id === contractId))
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
        <td width="80">
          <UiIfSet :value="!!item.client_groups_count">
            <template #empty>
              0 уч.
            </template>
            {{ item.client_groups_count }}
            уч.
          </UiIfSet>
          <div v-if="item.capacity" class="text-gray">
            {{ item.capacity }} max.
          </div>
        </td>
        <td width="160">
          <UiIfSet :value="Object.keys(item.teeth).length > 0">
            <template #empty>
              расписание отсутствует
            </template>
            <TeethAsText :items="item.teeth" />
          </UiIfSet>
        </td>
        <td width="30">
          <ScheduleDraftProblems :item="item" :contract-id="contractId" />
        </td>
        <td>
          <!-- Группа сейчас находится в ДРУГОМ договоре (нет действий, нет процесса) -->
          <template v-if="item.swamp && item.current_contract_id !== contractId">
            <v-switch :model-value="false" disabled />
          </template>

          <!-- Группа находится в ЭТОМ договоре (есть действия, есть процесс по по договору) -->
          <template v-else>
            <v-switch
              v-if="item.swamp"
              :model-value="true"
              @click="emit('removeFromGroup', item)"
            ></v-switch>
            <v-switch
              v-else
              :model-value="false"
              :disabled="isAlreadyInOtherGroup"
              @click="emit('addToGroup', item)"
            ></v-switch>
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
        margin-top: 10px !important;
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

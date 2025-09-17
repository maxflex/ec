<script setup lang="ts">
import type { ProjectGroup } from '.'
import { isGroupChangedInContract } from '.'

const { items, contractId } = defineProps<{
  items: ProjectGroup[]
  contractId: number
}>()

const emit = defineEmits<{
  addToGroup: [e: ProjectGroup]
  removeFromGroup: [e: ProjectGroup]
}>()

function getElementId(groupId: number, cId: number | null | undefined) {
  return `project-group-${groupId}${cId ? `-${cId}` : ''}`
}

const isAlreadyInOtherGroup = computed(() =>
  items.some(e => e.swamp && e.current_contract_id === contractId),
)
</script>

<template>
  <v-table class="table-padding project-group">
    <tbody>
      <tr
        v-for="item in items"
        :id="getElementId(item.id, contractId)"
        :key="item.id"
        :class="{ changed: isGroupChangedInContract(item, contractId!) }"
      >
        <td width="80">
          <GroupLink :item="item" />
        </td>
        <td width="190">
          <GroupTeachers :item="item" />
        </td>
        <td width="130">
          {{ ProgramShortLabel[item.program] }}
        </td>
        <td width="70">
          <GroupFirstLessonDate :date="item.first_lesson_date" />
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
          <GroupStudentsCount :item="item" />
        </td>
        <td width="160">
          <UiIfSet :value="Object.keys(item.teeth).length > 0">
            <template #empty>
              расписания нет
            </template>
            <TeethAsText :items="item.teeth" />
          </UiIfSet>
        </td>
        <td width="80">
          <div v-for="c in item.cabinets" :key="c">
            <CabinetWithCapacity :item="c" />
          </div>
        </td>
        <td width="30">
          <ProjectProblems :item="item" :contract-id="contractId" />
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
.project-group {
  td {
    box-sizing: content-box;
    position: relative;
    transition: none !important;
    &:last-child {
      .project-problems:not(:first-child) {
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

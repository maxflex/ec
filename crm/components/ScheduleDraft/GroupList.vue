<script setup lang="ts">
import type { ScheduleDraftGroup } from '.'
import { mdiArrowRightThin } from '@mdi/js'

const { items } = defineProps<{
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
</script>

<template>
  <v-table class="table-padding schedule-draft-group">
    <tbody>
      <tr
        v-for="item in items"
        :id="getElementId(item.id, contractId)"
        :key="item.id"
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

        <template v-if="item.swamp">
          <td v-if="item.swamp.contract_id !== contractId" class="text-gray">
            <UiIconLink @click="emit('jump-to-contract', item)">
              добавлен по договору №{{ item.swamp.contract_id }}
            </UiIconLink>
          </td>
          <td v-else :class="`swamp-status swamp-status--${item.swamp.status}`">
            <ScheduleDraftIcon v-if="item.draft_status">
              добавлен в черновике
            </ScheduleDraftIcon>

            <!-- в "проект по договору" не показываем -->
            <template v-if="item.swamp.id > 0">
              {{ item.swamp.lessons_conducted }}
              <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
              {{ item.swamp.total_lessons }}
            </template>
            <div>
              {{ SwampStatusLabel[item.swamp.status] }}
            </div>
            <div v-if="item.overlap?.count">
              {{ item.overlap!.count }} пересечений
              ({{ item.overlap!.programs.map(e => ProgramShortLabel[e]).join(', ') }})
            </div>
            <div v-if="item.uncunducted_count" class="text-error">
              {{ item.uncunducted_count }} непроведенных занятий
            </div>

            <div class="table-actionss">
              <v-btn color="error" density="comfortable" @click="emit('removeFromGroup', item)">
                <span class="text-white">
                  убрать из группы
                </span>
              </v-btn>
            </div>
          </td>
        </template>
        <template v-else>
          <td colspan="100">
            <ScheduleDraftIcon v-if="item.draft_status">
              убран в черновике
              <!-- , реально в группе -->
            </ScheduleDraftIcon>
            <template v-if="item.overlap?.count">
              {{ item.overlap!.count }} пересечений
              ({{ item.overlap!.programs.map(e => ProgramShortLabel[e]).join(', ') }})
            </template>
            <div class="table-actionss">
              <v-btn color="secondary" density="comfortable" @click="emit('addToGroup', item)">
                добавить в группу
              </v-btn>
            </div>
          </td>
        </template>
      </tr>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.schedule-draft-group {
  td {
    box-sizing: content-box;
    position: relative;
  }

  .table-actionss {
    top: 5px !important;
    width: 200px !important;
    .v-btn {
      width: 160px;
      font-size: 14px !important;
    }
  }

  .v-table__wrapper {
    overflow: hidden !important;
  }

  tr:last-child td {
    border-bottom: 1px solid rgb(var(--v-theme-border));
  }
}
</style>

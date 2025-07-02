<script setup lang="ts">
import type { ScheduleDraftGroup } from '.'
import { mdiArrowRightThin } from '@mdi/js'

const { items } = defineProps<{
  items: ScheduleDraftGroup[]
}>()

const emit = defineEmits<{
  addToGroup: [e: ScheduleDraftGroup]
  removeFromGroup: [e: ScheduleDraftGroup]
}>()
</script>

<template>
  <v-table class="table-padding schedule-draft-list">
    <tbody>
      <tr
        v-for="(item, i) in items"
        :id="`group-${item.id}`"
        :key="item.id"
        :class="{
          'group-list__separate': i + 1 < items.length && item.program !== items[i + 1].program,
        }"
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
        <template v-if="item.swamp">
          <td :class="`swamp-status--${item.swamp.status}`">
            <div class="pl-3">
              <div v-if="item.contract">
                {{ item.swamp.lessons_conducted }}
                <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
                {{ item.swamp.total_lessons }}
              </div>
              <div>
                {{ SwampStatusLabel[item.swamp.status] }}
              </div>

              <div v-if="item.overlap?.count">
                {{ item.overlap!.count }} пересечений
                ({{ item.overlap!.programs.map(e => ProgramShortLabel[e]).join(', ') }})
              </div>
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
            <div class="pl-3">
              <template v-if="item.overlap?.count">
                {{ item.overlap!.count }} пересечений
                ({{ item.overlap!.programs.map(e => ProgramShortLabel[e]).join(', ') }})
              </template>
            </div>
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
.schedule-draft-list {
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
}
</style>

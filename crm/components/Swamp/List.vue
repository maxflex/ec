<script setup lang="ts">
import type { SwampListResource } from '.'
import { mdiArrowRightThin } from '@mdi/js'

const { items } = defineProps<{
  items: SwampListResource[]
}>()
</script>

<template>
  <v-table class="swamp-list table-padding">
    <thead>
      <tr>
        <th width="80"></th>
        <th width="180"></th>
        <th width="150"></th>
        <th width="100"></th>
        <th width="60"></th>
        <th width="140"></th>
        <th width="100"></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in items" :key="item.id">
        <template v-if="item.group">
          <td width="80">
            <NuxtLink :to="{ name: 'groups-id', params: { id: item.group!.id } }">
              ГР-{{ item.group!.id }}
            </NuxtLink>
          </td>
          <td width="180">
            <GroupTeachers :item="item.group" />
          </td>
          <td>
            {{ ProgramShortLabel[item.program] }}
          </td>
          <td>
            <UiIfSet :value="item.group.lesson_counts.conducted || item.group.lesson_counts.planned">
              <template #empty>
                занятий нет
              </template>
              <GroupLessonCounts :item="item.group" />
            </UiIfSet>
          </td>
          <td>
            <UiIfSet :value="!!item.group.client_groups_count">
              <template #empty>
                0 уч.
              </template>
              {{ item.group.client_groups_count }} уч.
            </UiIfSet>
          </td>
          <td>
            <UiIfSet :value="Object.keys(item.group.teeth).length > 0">
              <template #empty>
                расписание отсутствует
              </template>
              <TeethAsText :items="item.group.teeth" />
            </UiIfSet>
          </td>
        </template>
        <template v-else>
          <td colspan="2" class="text-gray">
            Без группы
          </td>
          <td>
            {{ ProgramShortLabel[item.program] }}
          </td>
          <td colspan="3" />
        </template>
        <td colspan="2">
          <div class="pl-3">
            <div>
              {{ item.lessons_conducted }}
              <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
              {{ item.total_lessons }}
            </div>
            <div>
              {{ SwampStatusLabel[item.status] }}
            </div>
          </div>
          <div class="table-actionss">
          </div>
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.swamp-list {
  thead {
    visibility: hidden;
    th {
      height: 0 !important;
      border: 0 !important;
    }
  }

  th,
  td {
    box-sizing: content-box;
  }
}
</style>

<script setup lang="ts">
import type { GroupListResource } from '.'

const { items, blurOthers } = defineProps<{
  items: GroupListResource[]
  /**
   * Blur групп, где текущий препод больше не будет вести занятия
   * (препода нет в планируемых занятиях)
   */
  blurOthers?: number
}>()
</script>

<template>
  <v-table class="table-padding group-list">
    <tbody>
      <tr
        v-for="item in items"
        :id="`group-${item.id}`"
        :key="item.id"
        :class="{
          'group-list__item--blur': blurOthers && !item.teachers.map(e => e.id).includes(blurOthers),
        }"
      >
        <td width="80">
          <GroupLink :item="item" />
        </td>
        <td width="170">
          <GroupTeachers :item="item" />
        </td>
        <td width="140">
          {{ ProgramShortLabel[item.program] }}
        </td>
        <td width="80">
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
        <td width="140">
          <UiIfSet :value="Object.keys(item.teeth).length > 0">
            <template #empty>
              расписания нет
            </template>
            <TeethAsText :items="item.teeth" />
          </UiIfSet>
        </td>
        <slot :group="item">
        </slot>
        <td>
          <div v-for="c in item.cabinets" :key="c">
            <CabinetWithCapacity :item="c" />
          </div>
        </td>
        <td>
          <div v-if="item.zoom && item.zoom.id">
            {{ item.zoom.id }} <br />
            {{ item.zoom.password }}
          </div>
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

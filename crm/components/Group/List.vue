<script setup lang="ts">
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
        v-for="(item, i) in items"
        :id="`group-${item.id}`"
        :key="item.id"
        :class="{
          'group-list__separate': i + 1 < items.length && item.program !== items[i + 1].program,
          'group-list__item--blur': blurOthers && !item.teachers.map(e => e.id).includes(blurOthers),
        }"
      >
        <td width="80">
          <NuxtLink :to="{ name: 'groups-id', params: { id: item.id } }">
            ГР-{{ item.id }}
          </NuxtLink>
        </td>
        <td width="180">
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
        <td width="60">
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
        <slot :group="item">
        </slot>
        <td v-if="!('default' in $slots)">
          <div v-if="item.zoom.id" class="group-list__zoom">
            {{ item.zoom.id }} / {{ item.zoom.password }}
          </div>
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

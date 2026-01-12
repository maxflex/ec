<script setup lang="ts">
import type { GroupListResource } from '.'

const { items, selectable } = defineProps<{
  items: GroupListResource[]
  selectable?: boolean
  // blur групп, где текущий препод больше не ведёт занятия
  // (другими словами, препода нет в планируемых занятиях)
  blurOthers?: number
}>()

const emit = defineEmits<{
  select: [g: GroupListResource]
}>()

function onClick(g: GroupListResource) {
  if (selectable) {
    emit('select', g)
  }
}
</script>

<template>
  <Table
    class="table--padding group-list-teacher group-list"
    :class="{
      'group-list--selectable': selectable,
    }"
    :hoverable="selectable"
  >
    <TableRow
      v-for="item in items"
      :id="`group-${item.id}`"
      :key="item.id"
      :class="{
        'group-list__item--blur': blurOthers && !item.teachers.map(e => e.id).includes(blurOthers),
      }"
      @click="onClick(item)"
    >
      <TableCol :width="80">
        <NuxtLink :to="{ name: 'groups-id', params: { id: item.id } }">
          ГР-{{ item.id }}
        </NuxtLink>
      </TableCol>
      <TableCol :width="180">
        <GroupTeachers :item="item" />
      </TableCol>
      <TableCol :width="120">
        {{ ProgramShortLabel[item.program] }}
      </TableCol>
      <TableCol :width="80">
        <GroupFirstLessonDate :date="item.first_lesson_date" />
      </TableCol>
      <TableCol :width="140">
        <GroupLessonCounts :item="item" sum-free />
      </TableCol>
      <TableCol :width="60">
        {{ item.client_groups_count }} уч.
      </TableCol>
      <TableCol>
        <TeethAsText :items="item.teeth" />
      </TableCol>
      <TableCol :width="100">
        <div v-for="c in item.cabinets" :key="c">
          <CabinetWithCapacity :item="c" />
        </div>
      </TableCol>
      <TableCol class="group-list__zoom">
        <template v-if="item.zoom?.id">
          {{ item.zoom.id }} / {{ item.zoom.password }}
        </template>
      </TableCol>
    </TableRow>
  </Table>
</template>

<style lang="scss">
.group-list-teacher {
  & > div {
    align-items: flex-start !important;
  }
}
</style>

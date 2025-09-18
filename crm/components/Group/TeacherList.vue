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
  <div
    class="table table--padding group-list-teacher group-list"
    :class="{
      'group-list--selectable table--hover': selectable,
    }"
  >
    <div
      v-for="item in items"
      :id="`group-${item.id}`"
      :key="item.id"
      :class="{
        'group-list__item--blur': blurOthers && !item.teachers.map(e => e.id).includes(blurOthers),
      }"
      @click="onClick(item)"
    >
      <div style="width: 80px">
        <NuxtLink :to="{ name: 'groups-id', params: { id: item.id } }">
          ГР-{{ item.id }}
        </NuxtLink>
      </div>
      <div style="width: 180px">
        <GroupTeachers :item="item" />
      </div>
      <div style="width: 120px">
        {{ ProgramShortLabel[item.program] }}
      </div>
      <div style="width: 80px">
        <GroupFirstLessonDate :date="item.first_lesson_date" />
      </div>
      <div style="width: 140px">
        <GroupLessonCounts :item="item" sum-free />
      </div>
      <div style="width: 60px">
        {{ item.client_groups_count }} уч.
      </div>
      <div style="flex: 1">
        <TeethAsText :items="item.teeth" />
      </div>
      <div style="width: 100px">
        <div v-for="c in item.cabinets" :key="c">
          <CabinetWithCapacity :item="c" />
        </div>
      </div>
      <div class="group-list__zoom">
        <template v-if="item.zoom?.id">
          {{ item.zoom.id }} / {{ item.zoom.password }}
        </template>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.group-list-teacher {
  & > div {
    align-items: flex-start !important;
  }
}
</style>

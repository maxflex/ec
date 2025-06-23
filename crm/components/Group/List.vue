<script setup lang="ts">
const { items, selectable, blurOthers } = defineProps<{
  items: GroupListResource[]
  selectable?: boolean
  // blur групп, где текущий препод больше не ведёт занятия
  // (другими словами, препода нет в планируемых занятиях)
  blurOthers?: number
}>()

const emit = defineEmits<{
  selected: [g: GroupListResource]
}>()

function onClick(g: GroupListResource) {
  if (selectable) {
    emit('selected', g)
  }
}
</script>

<template>
  <div
    class="table table--padding group-list"
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
      <div style="width: 150px">
        {{ ProgramShortLabel[item.program] }}
      </div>
      <div style="width: 140px">
        <UiIfSet :value="item.lesson_counts.conducted || item.lesson_counts.planned">
          <template #empty>
            занятий нет
          </template>
          <GroupLessonCounts :item="item" />
        </UiIfSet>
      </div>
      <div style="width: 60px">
        <UiIfSet :value="!!item.client_groups_count">
          <template #empty>
            0 уч.
          </template>
          {{ item.client_groups_count }} уч.
        </UiIfSet>
      </div>
      <div style="flex: 1">
        <UiIfSet :value="Object.keys(item.teeth).length > 0">
          <template #empty>
            расписание отсутствует
          </template>
          <TeethAsText :items="item.teeth" />
        </UiIfSet>
      </div>
      <div class="group-list__zoom">
        <template v-if="item.zoom.id">
          {{ item.zoom.id }} / {{ item.zoom.password }}
        </template>
      </div>
    </div>
  </div>
</template>

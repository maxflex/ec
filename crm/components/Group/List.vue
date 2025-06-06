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
        <template v-if="item.teachers.length">
          <div v-for="t in item.teachers" :key="t.id">
            <UiPerson :item="t" no-link />
          </div>
        </template>
        <span v-else class="text-gray">
          преподавателей нет
        </span>
      </div>
      <div style="width: 150px">
        {{ ProgramShortLabel[item.program] }}
      </div>
      <div style="width: 140px">
        <GroupLessonCounts v-if="item.lessons.conducted || item.lessons.planned" :item="item" />
        <span v-else class="text-gray">
          занятий нет
        </span>
      </div>
      <div style="width: 60px">
        <template v-if="item.client_groups_count">
          {{ item.client_groups_count }} уч.
        </template>
        <span v-else class="text-gray">
          0 уч.
        </span>
      </div>
      <div style="flex: 1">
        <TeethAsText v-if="Object.keys(item.teeth).length" :items="item.teeth" />
        <span v-else class="text-gray">
          расписание отсутствует
        </span>
      </div>
      <div class="group-list__zoom">
        <span>
          {{ item.zoom.id }}
        </span>
        <span>
          {{ item.zoom.password }}
        </span>
      </div>
    </div>
  </div>
</template>

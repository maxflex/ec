<script setup lang="ts">
const { items, selectable } = defineProps<{
  items: GroupListResource[]
  selectable?: boolean
  // blur групп, где текущий препод больше не ведёт занятия
  // (другими словами, препода нет в планируемых занятиях)
  blurOthers?: boolean
}>()

const emit = defineEmits<{
  selected: [g: GroupListResource]
}>()

const { user } = useAuthStore()

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
        'group-list__item--blur': blurOthers && !item.teachers.map(e => e.id).includes(user!.id),
      }"
      @click="onClick(item)"
    >
      <div style="width: 80px">
        <NuxtLink :to="{ name: 'groups-id', params: { id: item.id } }">
          ГР-{{ item.id }}
        </NuxtLink>
      </div>
      <div style="width: 180px">
        <div v-for="t in item.teachers" :key="t.id">
          <UiPerson :item="t" no-link />
        </div>
      </div>
      <div style="width: 150px">
        {{ ProgramShortLabel[item.program] }}
      </div>
      <div style="width: 140px">
        <GroupLessonCounts :item="item" />
      </div>
      <div style="width: 60px">
        {{ item.client_groups_count }} уч.
      </div>
      <div style="flex: 1">
        <TeethAsText :items="item.teeth" />
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

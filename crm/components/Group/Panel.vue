<script setup lang="ts">
const { item } = defineProps<{
  item: GroupResource
}>()
</script>

<template>
  <div class="panel-info">
    <div>
      <h2 style="font-size: 28px">
        Группа {{ item.id }}
      </h2>
    </div>

    <div v-if="item.teachers.length">
      <div>преподаватели</div>
      <div v-for="t in item.teachers" :key="t.id">
        <RouterLink :to="{ name: 'teachers-id', params: { id: t.id } }">
          {{ formatNameInitials(t) }}
        </RouterLink>
        <span class="teacher-lesson-counts">
          {{ item.counts_by_teacher[t.id] }}
        </span>
      </div>
    </div>
    <div v-else>
      <div></div>
      <div class="text-gray">
        преподавателей нет
      </div>
    </div>
    <div>
      <div>программа</div>
      <div v-if="item.program">
        {{ ProgramLabel[item.program] }}
      </div>
    </div>

    <div>
      <div>уроки</div>
      <div v-if="item.lessons.conducted || item.lessons.planned">
        <GroupLessonCounts :item="item" />
      </div>
      <div v-else class="text-gray">
        уроков нет
      </div>
    </div>

    <div>
      <div>
        zoom
      </div>
      <div>
        <UiIfSet :value="item.zoom.id">
          {{ item.zoom.id }} / {{ item.zoom.password }}
        </UiIfSet>
      </div>
    </div>

    <div class="panel-actions">
      <slot name="actions" />
    </div>
  </div>
</template>

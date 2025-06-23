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
          {{ item.teacher_counts[t.id] }}
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
      <div>
        <UiIfSet :value="item.lesson_counts.conducted || item.lesson_counts.planned">
          <template #empty>
            уроков нет
          </template>
          <GroupLessonCounts :item="item" />
        </UiIfSet>
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

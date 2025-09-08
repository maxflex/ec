<script setup lang="ts">
import type { SwampListResource } from '.'

const { item, changes } = defineProps<{
  item: SwampListResource
  changes?: boolean
}>()

const { group } = changes ? item.changes! : item
</script>

<template>
  <div v-if="group">
    <div>
      <GroupLink :item="group" />
    </div>
    <div>
      <GroupTeachers :item="group" />
    </div>
    <div>
      {{ ProgramShortLabel[item.program] }}
    </div>
    <div>
      <GroupFirstLessonDate :date="group.first_lesson_date" />
    </div>
    <div>
      <UiIfSet :value="group.lesson_counts.conducted || group.lesson_counts.planned">
        <template #empty>
          занятий нет
        </template>
        <GroupLessonCounts :item="group" />
      </UiIfSet>
    </div>
    <div>
      <GroupStudentsCount v-if="group" :item="group" />
    </div>
    <div>
      <UiIfSet :value="Object.keys(group.teeth).length > 0">
        <template #empty>
          расписание отсутствует
        </template>
        <TeethAsText :items="group.teeth" />
      </UiIfSet>
    </div>
    <div>
      <div v-for="c in group.cabinets" :key="c">
        <CabinetWithCapacity :item="c" />
      </div>
    </div>

    <div>
      <ContractVersionProgramStatus :item="item" />
      <div v-if="item.changes">
        <template v-if="item.changes.type === 'added'">
          добавлен в
        </template>
        <template v-else-if="item.changes.type === 'changed'">
          перемещён в
        </template>
        <template v-else>
          уходит из группы
        </template>
        <RouterLink v-if="item.changes.group" :to="{ name: 'groups-id', params: { id: item.changes.group.id } }">
          ГР-{{ item.changes.group.id }}
        </RouterLink>
        <br />
        в проекте

        <RouterLink
          :to="{
            name: 'schedule-drafts-editor',
            query: {
              id: item.changes.schedule_draft_id,
            },
          }"
        >
          {{ item.changes.schedule_draft_id }}
        </RouterLink>
      </div>
    </div>
  </div>
</template>

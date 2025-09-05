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
    </div>
  </div>
</template>

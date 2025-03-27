<script setup lang="ts">
import type { TeacherDialog } from '#components'
import type { TeacherFilters } from '~/components/Teacher/Filters.vue'

const filters = ref<TeacherFilters>(loadFilters({
  subjects: [],
}))
const teacherDialog = ref<InstanceType<typeof TeacherDialog>>()

const { items, indexPageData } = useIndex<TeacherListResource, TeacherFilters>(
  `teachers`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <TeacherFilters v-model="filters" />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="teacherDialog?.create()">
        добавить преподавателя
      </v-btn>
    </template>

    <TeacherList :items="items" />
  </UiIndexPage>
  <TeacherDialog ref="teacherDialog" />
</template>

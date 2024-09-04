<script setup lang="ts">
import type { TeacherDialog } from '#components'
import type { Filters } from '~/components/Teacher/Filters.vue'

const teacherDialog = ref<InstanceType<typeof TeacherDialog>>()

const { items, loading, onFiltersApply } = useIndex<TeacherListResource, Filters>(
    `teachers`,
)
</script>

<template>
  <UiFilters>
    <TeacherFilters @apply="onFiltersApply" />
    <template #buttons>
      <v-btn color="primary" @click="teacherDialog?.create()">
        добавить преподавателя
      </v-btn>
    </template>
  </UiFilters>
  <div>
    <UiLoader3 :loading="loading" />
    <TeacherList :items="items" />
  </div>
  <TeacherDialog ref="teacherDialog" />
</template>

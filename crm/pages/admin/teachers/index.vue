<script setup lang="ts">
import type { TeacherDialog } from '#components'
import type { Filters } from '~/components/Teacher/Filters.vue'

const teacherDialog = ref<InstanceType<typeof TeacherDialog>>()

const { items, loading, onFiltersApply } = useIndex<TeacherListResource, Filters>(
    `teachers`,
)
</script>

<template>
  <div class="filters">
    <TeacherFilters @apply="onFiltersApply" />
    <v-btn color="primary" @click="teacherDialog?.create()">
      добавить преподавателя
    </v-btn>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <TeacherList :items="items" />
  </div>
  <TeacherDialog ref="teacherDialog" />
</template>

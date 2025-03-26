<script setup lang="ts">
import type { TeacherServiceDialog } from '#build/components'
import { apiUrl, type TeacherServiceResource } from '.'

const { teacherId } = defineProps<{ teacherId: number }>()
const teacherServiceDialog = ref<InstanceType<typeof TeacherServiceDialog>>()
const filters = ref<AvailableYearsFilter>({
  year: undefined,
})

const { items, availableYears, indexPageData } = useIndex<TeacherServiceResource, AvailableYearsFilter>(
  apiUrl,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      teacher_id: teacherId,
    },
  },
)

function onUpdated(p: TeacherServiceResource) {
  const index = items.value.findIndex(e => e.id === p.id)
  if (index !== -1) {
    items.value[index] = p
  }
  else {
    items.value.unshift(p)
  }
  itemUpdated('teacher-services', p.id)
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector
        v-model="filters.year"
        :items="availableYears"
      />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        @click="teacherServiceDialog?.create(teacherId)"
      >
        добавить допуслугу
      </v-btn>
    </template>
    <TeacherServiceList :items="items" @open="teacherServiceDialog?.edit" />
  </UiIndexPage>
  <TeacherServiceDialog ref="teacherServiceDialog" @updated="onUpdated" />
</template>

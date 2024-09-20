<script setup lang="ts">
import type { TeacherServiceDialog } from '#build/components'

const { teacherId } = defineProps<{ teacherId: number }>()
const teacherServiceDialog = ref<InstanceType<typeof TeacherServiceDialog>>()
const tabName = 'TeacherServiceTab'
const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}, tabName))
const { items, indexPageData } = useIndex<TeacherServiceResource, YearFilters>(
    `teacher-services`,
    filters,
    {
      tabName,
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
      <v-select
        v-model="filters.year" :items="selectItems(YearLabel)" label="Год"
        density="comfortable"
      />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        @click="teacherServiceDialog?.create(teacherId, filters.year)"
      >
        добавить платеж
      </v-btn>
    </template>
    <TeacherServiceList :items="items" @open="teacherServiceDialog?.edit" />
  </UiIndexPage>
  <TeacherServiceDialog ref="teacherServiceDialog" @updated="onUpdated" />
</template>

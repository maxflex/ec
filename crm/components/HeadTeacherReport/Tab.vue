<script setup lang="ts">
import { HeadTeacherReportDialog } from '#components'

const { teacherId } = defineProps<{
  teacherId: number
}>()

const tabName = 'HeadTeacherReportTab'
const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}, tabName))
const { items, indexPageData } = useIndex<HeadTeacherReportResource, YearFilters>(
  `head-teacher-reports`,
  filters,
  {
    tabName,
    staticFilters: {
      teacher_id: teacherId,
      requirement: 'created',
    },
  },
)
const dialogRef = ref<InstanceType<typeof HeadTeacherReportDialog>>()

function onUpdated(item: HeadTeacherReportResource) {
  const index = items.value.findIndex(e => e.id === item.id)
  if (index === -1) {
    items.value.unshift(item)
  }
  else {
    items.value.splice(index, 1, item)
  }
  itemUpdated('head-teacher-report', item.id)
}

function onDeleted(item: HeadTeacherReportResource) {
  const index = items.value.findIndex(e => e.id === item.id)
  items.value.splice(index, 1)
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiYearSelector v-model="filters.year" density="comfortable" />
    </template>
    <HeadTeacherReportList :items="items" @edit="dialogRef?.edit" />
  </UiIndexPage>
  <HeadTeacherReportDialog ref="dialogRef" @updated="onUpdated" @deleted="onDeleted" />
</template>

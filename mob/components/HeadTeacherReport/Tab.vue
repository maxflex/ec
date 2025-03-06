<script setup lang="ts">
import { AvailableYearsSelector2, HeadTeacherReportDialog } from '#components'

const { teacherId } = defineProps<{
  teacherId: number
}>()

const filters = ref<AvailableYearsFilter>({
  year: undefined,
})

const { items, indexPageData, availableYears } = useIndex<HeadTeacherReportResource, AvailableYearsFilter>(
  `head-teacher-reports`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      teacher_id: teacherId,
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
      <AvailableYearsSelector2 v-model="filters.year" :items="availableYears" />
    </template>
    <HeadTeacherReportList :items="items" @edit="dialogRef?.edit" />
  </UiIndexPage>
  <HeadTeacherReportDialog ref="dialogRef" @updated="onUpdated" @deleted="onDeleted" />
</template>

<script setup lang="ts">
import type { HeadTeacherReportResource } from '~/components/HeadTeacherReport'
import { HeadTeacherReportDialog } from '#components'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<HeadTeacherReportResource, YearFilters>(
  `head-teacher-reports`,
  filters,
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
    <template #buttons>
      <v-btn color="primary" @click="dialogRef?.create()">
        создать отчёт
      </v-btn>
    </template>
    <HeadTeacherReportList :items="items" @edit="dialogRef?.edit" />
  </UiIndexPage>
  <HeadTeacherReportDialog ref="dialogRef" @updated="onUpdated" @deleted="onDeleted" />
</template>

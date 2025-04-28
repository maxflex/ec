<script setup lang="ts">
import type { TeacherServiceResource } from '~/components/TeacherService'

const filters = ref<{ year: Year }>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<TeacherServiceResource>(
  `teacher-services`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" density="comfortable" />
    </template>
    <TeacherServiceList :items="items" />
  </UiIndexPage>
</template>

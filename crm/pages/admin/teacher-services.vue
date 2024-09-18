<script setup lang="ts">
const filters = ref<{ year: Year }>({
  year: currentAcademicYear(),
})

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

<script setup lang="ts">
const filters = ref<GradeFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<QuartersGradesResource, GradeFilters>(
  `grades`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <GradeFilters v-model="filters" />
    </template>
    <GradeList :items="items" />
  </UiIndexPage>
</template>

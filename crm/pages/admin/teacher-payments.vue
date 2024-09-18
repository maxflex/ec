<script setup lang="ts">
const filters = ref<TeacherPaymentFilters>({
  year: currentAcademicYear(),
})
const { items, indexPageData } = useIndex<TeacherPaymentResource, TeacherPaymentFilters>(
    `teacher-payments`,
    filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <TeacherPaymentFilters v-model="filters" />
    </template>
    <TeacherPaymentList :items="items" />
  </UiIndexPage>
</template>

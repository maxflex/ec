<script setup lang="ts">
import type { TeacherPaymentResource } from '~/components/TeacherPayment'
import type { TeacherPaymentFilters } from '~/components/TeacherPayment/Filters.vue'

const filters = ref<TeacherPaymentFilters>(loadFilters({
  year: currentAcademicYear(),
}))
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

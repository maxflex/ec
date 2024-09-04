<script setup lang="ts">
import type { Filters } from '~/components/TeacherPayment/Filters.vue'

const { items, loading, onFiltersApply } = useIndex<TeacherPaymentResource, Filters>(
    `teacher-payments`,
    {
      defaultFilters: {
        year: currentAcademicYear(),
      },
    },
)
</script>

<template>
  <UiFilters>
    <TeacherPaymentFilters @apply="onFiltersApply" />
  </UiFilters>
  <UiLoader3 :loading="loading" />
  <TeacherPaymentList :items="items" />
</template>

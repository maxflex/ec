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
  <div class="filters">
    <TeacherPaymentFilters @apply="onFiltersApply" />
  </div>
  <UiLoader3 :loading="loading" />
  <TeacherPaymentList :items="items" />
</template>

<script setup lang="ts">
import { ReportRequirementLabel } from '~/utils/labels'

interface Filters {
  year: Year
  requirement?: ReportRequirement
}

const { user } = useAuthStore()

const filters = ref<Filters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<ReportListResource, Filters>(
  `reports`,
  filters,
  {
    staticFilters: {
      teacher_id: user?.id,
      exclude_not_required: 1,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Год"
        density="comfortable"
      />
      <UiClearableSelect
        v-model="filters.requirement"
        label="Тип"
        :items="selectItems(ReportRequirementLabel)"
        density="comfortable"
      />
    </template>
    <ReportListForTeachers :items="items" />
  </UiIndexPage>
</template>

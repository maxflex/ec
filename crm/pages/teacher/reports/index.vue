<script setup lang="ts">
interface Filters {
  year?: undefined
  requirement?: ReportRequirement
}
const { user } = useAuthStore()

const filters = ref<Filters>({
  year: undefined,
})

const { items, indexPageData, availableYears } = useIndex<ReportListResource, Filters>(
  `reports`,
  filters,
  {
    loadAvailableYears: true,
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
      <AvailableYearsSelector2 v-model="filters.year" :items="availableYears" />
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

<script setup lang="ts">
interface Filters {
  year?: undefined
  requirement?: ReportRequirement
}
const { user } = useAuthStore()
const availableYearsLoaded = ref(false)
const filters = ref<Filters>({})
const noData = computed(() => availableYearsLoaded.value && !filters.value.year)

const { items, indexPageData } = useIndex<ReportListResource, Filters>(
  `reports`,
  filters,
  {
    instantLoad: false,
    staticFilters: {
      teacher_id: user?.id,
      exclude_not_required: 1,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="noData ? { noData, loading: false } : indexPageData">
    <template #filters>
      <AvailableYearsSelector
        v-model="filters.year"
        :teacher-id="user!.id"
        mode="reports"
        @loaded="availableYearsLoaded = true"
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

<script setup lang="ts">
const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()

interface Filters extends AvailableYearsFilter {
  requirement?: ReportRequirement
}

// isHeadTeacher
const { isTeacher } = useAuthStore()

const filters = ref<Filters>({
  year: undefined,
})

const { items, availableYears, indexPageData } = useIndex<ReportListResource>(
  `reports`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      client_id: clientId,
      teacher_id: teacherId,
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
    <ReportListForHeadTeachers v-if="isTeacher" :items="items" />
    <ReportListForAdmins v-else :items="items" />
  </UiIndexPage>
</template>

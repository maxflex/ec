<script setup lang="ts">
/**
 * Вкладка "отчёты".
 *
 * Может отображаться у админа в профиле клиента или преподавателя
 * Может отобажаться у классрука в профиле клиента
 */

const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()

// отображается у классрука в профиле клиента?
const { isTeacher } = useAuthStore()

const filters = ref<{
  year?: Year
  requirement?: ReportRequirement
}>({
  year: undefined,
})

const { availableYears, items, indexPageData } = useIndex<ReportListResource>(
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

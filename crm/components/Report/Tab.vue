<script setup lang="ts">
import type { ReportListResource } from '.'

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
  is_required?: number
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
      paginate: 999,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
      <UiClearableSelect
        v-model="filters.is_required"
        label="Тип"
        :items="yesNo('только требования', 'только созданные')"
        density="comfortable"
      />
    </template>
    <ReportListForHeadTeachers v-if="isTeacher" :items="items" />
    <ReportListForAdmins v-else :items="items" />
  </UiIndexPage>
</template>

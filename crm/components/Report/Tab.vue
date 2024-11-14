<script setup lang="ts">
const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()
const tabName = clientId ? 'ClientReportTab' : 'TeacherReportTab'

// isHeadTeacher
const { isTeacher } = useAuthStore()

const filters = ref<{
  year: Year
  type?: number
}>(loadFilters({
  year: currentAcademicYear(),
}, tabName))
const loading = ref(true)
const items = ref<ReportListResource[]>([])

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<ReportListResource>>(
    `reports`,
    {
      params: {
        ...filters.value,
        client_id: clientId,
        teacher_id: teacherId,
      },
    },
  )
  if (data.value) {
    items.value = data.value.data
  }
  loading.value = false
}

watch(filters, (newVal) => {
  saveFilters(newVal, tabName)
  loadData()
}, { deep: true })

const noData = computed(() => !loading.value && items.value.length === 0)

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <template #filters>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Год"
        density="comfortable"
      />
      <UiClearableSelect
        v-model="filters.type"
        label="Тип"
        :items="yesNo('созданные', 'требуется отчёт')"
        density="comfortable"
      />
    </template>

    <ReportListForHeadTeachers v-if="isTeacher" :items="items" />
    <ReportListForAdmins v-else :items="items" />
  </UiIndexPage>
</template>

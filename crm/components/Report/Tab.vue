<script setup lang="ts">
const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()

// isHeadTeacher
const { isTeacher } = useAuthStore()
const selectedYear = ref<Year>()
const requirement = ref<ReportRequirement>()
const availableYearsLoaded = ref(false)

const loading = ref(false)
const items = ref<ReportListResource[]>([])

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<ReportListResource>>(
    `reports`,
    {
      params: {
        client_id: clientId,
        teacher_id: teacherId,
        year: selectedYear.value,
        requirement: requirement.value,
      },
    },
  )
  if (data.value) {
    items.value = data.value.data
  }
  loading.value = false
}

const noData = computed(() => availableYearsLoaded.value && !selectedYear.value)

function onAvailableYearsLoaded() {
  availableYearsLoaded.value = true
  // подгружаем данные только если есть какой-то год
  if (selectedYear.value) {
    loadData()
    watch(selectedYear, loadData)
  }
}
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <template #filters>
      <YearSelector
        v-model="selectedYear"
        :client-id="clientId"
        :teacher-id="teacherId"
        mode="reports"
        @loaded="onAvailableYearsLoaded()"
      />
      <UiClearableSelect
        v-model="requirement"
        label="Тип"
        :items="selectItems(ReportRequirementLabel)"
        density="comfortable"
        @update:model-value="loadData()"
      />
    </template>
    <ReportListForHeadTeachers v-if="isTeacher" :items="items" />
    <ReportListForAdmins v-else :items="items" />
  </UiIndexPage>
</template>

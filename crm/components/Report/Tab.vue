<script setup lang="ts">
const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()

// isHeadTeacher
const { isTeacher } = useAuthStore()
const year = ref<Year>()

const loading = ref(true)
const items = ref<ReportListResource[]>([])

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<ReportListResource>>(
    `reports`,
    {
      params: {
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

// watch(filters, (newVal) => {
//   loadData()
// }, { deep: true })

const noData = computed(() => !loading.value && items.value.length === 0)

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <template #filters>
      <YearSelector v-model="year" :client-id="clientId" mode="reports" />
      <!-- <UiClearableSelect
        v-model="filters.requirement"
        label="Тип"
        :items="selectItems(ReportRequirementLabel)"
        density="comfortable"
      /> -->
    </template>

    <ReportListForHeadTeachers v-if="isTeacher" :items="items" />
    <ReportListForAdmins v-else :items="items" />
  </UiIndexPage>
</template>

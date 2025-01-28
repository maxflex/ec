<script setup lang="ts">
const { clientId } = defineProps<{
  clientId?: number
}>()
const tabName = 'HeadTeacherReportTab'

const filters = ref<{
  year: Year
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
    </template>
    <ReportListForHeadTeachers :items="items" />
  </UiIndexPage>
</template>

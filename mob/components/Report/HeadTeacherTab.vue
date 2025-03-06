<script setup lang="ts">
const { clientId } = defineProps<{
  clientId: number
}>()

const selectedYear = ref<Year>()
const availableYearsLoaded = ref(false)
const loading = ref(true)
const items = ref<ReportListResource[]>([])

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<ReportListResource>>(
    `reports`,
    {
      params: {
        client_id: clientId,
        requirement: 'created',
        year: selectedYear.value,
      },
    },
  )
  if (data.value) {
    items.value = data.value.data
  }
  loading.value = false
}

function onAvailableYearsLoaded() {
  availableYearsLoaded.value = true
  // подгружаем данные только если есть какой-то год
  if (selectedYear.value) {
    loadData()
    watch(selectedYear, loadData)
  }
}

const noData = computed(() => {
  if (selectedYear.value) {
    return !loading.value && items.value.length === 0
  }
  return availableYearsLoaded.value && !selectedYear.value
})
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <template #filters>
      <AvailableYearsSelector
        v-model="selectedYear"
        :client-id="clientId"
        mode="reports"
        @loaded="onAvailableYearsLoaded()"
      />
    </template>
    <ReportListForHeadTeachers :items="items" />
  </UiIndexPage>
</template>

<script setup lang="ts">
const { clientId } = defineProps<{ clientId: number }>()
const tabName = 'ClientReviewTab'
const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}, tabName))
const loading = ref(true)
const items = ref<ClientReviewListResource[]>([])

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<ClientReviewListResource[]>>(
      `client-reviews`,
      {
        params: {
          ...filters.value,
          client_id: clientId,
          with: 'client',
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
    <ClientReviewList :items="items" />
  </UiIndexPage>
</template>

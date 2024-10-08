<script setup lang="ts">
const { clientId } = defineProps<{ clientId: number }>()

const tabName = 'GroupsTab'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}, tabName))

const loading = ref(true)
const swamps = ref<SwampListResource[]>([])
const noData = computed(() => !loading.value && swamps.value.length === 0)

async function loadData() {
  loading.value = true
  await loadSwamps()
  loading.value = false
}

async function loadSwamps() {
  const params = {
    ...filters.value,
    client_id: clientId,
  }
  const { data } = await useHttp<ApiResponse<SwampListResource[]>>(`swamps`, { params })
  if (data.value) {
    swamps.value = data.value.data
  }
}

watch(filters, (newVal) => {
  loadData()
  saveFilters(newVal, tabName)
}, { deep: true })

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <template #filters>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Учебный год"
        density="comfortable"
      />
    </template>
    <SwampList :items="swamps" @add="loadData()" />
  </UiIndexPage>
</template>

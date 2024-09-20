<script setup lang="ts">
const { clientId } = defineProps<{
  clientId: number
}>()

const filters = ref(loadFilters({
  year: currentAcademicYear(),
}, 'GroupsTab'))

const loading = ref(true)
const groups = ref<GroupListResource[]>([])
const swamps = ref<SwampListResource[]>([])
const params = {
  ...filters.value,
  client_id: clientId,
}

async function loadData() {
  loading.value = true
  await loadGroups()
  await loadSwamps()
  loading.value = false
}

async function loadGroups() {
  const { data } = await useHttp<ApiResponse<GroupListResource[]>>(`groups`, { params })
  if (data.value) {
    groups.value = data.value.data
  }
}

function onSelected() {
  console.log('here')
}

async function loadSwamps() {
  const { data } = await useHttp<ApiResponse<SwampListResource[]>>(`swamps`, { params })
  if (data.value) {
    swamps.value = data.value.data
  }
}

watch(filters, (newVal) => {
  loadData()
  saveFilters(newVal, 'GroupsTab')
}, { deep: true })

const noData = computed(() => !loading.value && groups.value.length === 0 && swamps.value.length === 0)

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }" class="separate-content">
    <template #filters>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Учебный год"
        density="comfortable"
      />
    </template>
    <div class="table table--padding">
      <GroupList :items="groups" />
    </div>
    <SwampList :items="swamps" @select="onSelected" />
  </UiIndexPage>
</template>

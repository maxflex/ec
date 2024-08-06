<script setup lang="ts">
const { clientId } = defineProps<{
  clientId: number
}>()

const loading = ref(false)
const year = ref<Year>(currentAcademicYear())
const groups = ref<GroupListResource[]>([])
const swamps = ref<SwampListResource[]>([])
const params = {
  year: year.value,
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
  const status: SwampFilterStatus = 'toFulfil'
  const { data } = await useHttp<ApiResponse<SwampListResource[]>>(`swamps`, {
    params: {
      ...params,
      status,
    },
  })
  if (data.value) {
    swamps.value = data.value.data
  }
}

watch(year, (newVal) => {
  params.year = newVal
  loadData()
})

nextTick(loadData)
</script>

<template>
  <div class="filters">
    <div class="filters-inputs">
      <v-select v-model="year" :items="selectItems(YearLabel)" label="Учебный год" density="comfortable" />
    </div>
  </div>
  <template v-if="!loading">
    <div class="table table--padding">
      <GroupList :items="groups" />
    </div>
    <SwampList :items="swamps" @select="onSelected" />
  </template>
</template>

<style scoped lang="scss">

</style>

<script setup lang="ts">
const { clientId } = defineProps<{ clientId: number }>()

const tabName = 'GroupsTab'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}, tabName))

const groupFilters = ref<GroupFilters>({
  year: currentAcademicYear(),
  program: [],
})

const loading = ref(true)
const selectedSwampId = ref<number>()
const swamps = ref<SwampListResource[]>([])
const clientGroups = ref<GroupListResource[]>([])
const noData = computed(() => !loading.value && clientGroups.value.length === 0 && swamps.value.length === 0)

const { items: groups } = useIndex<GroupListResource, GroupFilters>(
  `groups`,
  groupFilters,
  {
    instantLoad: false,
    disableSaveFilters: true,
  },
)
async function loadData() {
  loading.value = true
  await loadGroups()
  await loadSwamps()
  loading.value = false
}

async function loadSwamps() {
  loading.value = true
  const params = {
    ...filters.value,
    status: 'noGroup',
    client_id: clientId,
  }
  const { data } = await useHttp<ApiResponse<SwampListResource>>(`swamps`, { params })
  if (data.value) {
    swamps.value = data.value.data
  }
  loading.value = false
}

function onAttachStart(swamp: SwampListResource) {
  selectedSwampId.value = swamp.id
  groupFilters.value = {
    year: filters.value.year,
    program: [swamp.program],
  }
}

async function loadGroups() {
  const params = {
    ...filters.value,
    client_id: clientId,
  }
  const { data } = await useHttp<ApiResponse<GroupListResource>>(`groups`, { params })
  if (data.value) {
    clientGroups.value = data.value.data
  }
}

async function onGroupSelected(group: GroupListResource) {
  await useHttp(`client-groups`, {
    method: 'post',
    params: {
      group_id: group.id,
      contract_version_program_id: selectedSwampId.value!,
    },
  })
  back()
  await loadData()
}

function back() {
  selectedSwampId.value = undefined
}

watch(filters, (newVal) => {
  loadData()
  saveFilters(newVal, tabName)
}, { deep: true })

nextTick(loadData)
</script>

<template>
  <UiIndexPage v-if="selectedSwampId" :data="{ loading, noData }">
    <template #filters>
      <GroupFilters v-model="groupFilters" disabled />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="back()">
        <template #prepend>
          <v-icon icon="$back" />
        </template>
        назад
      </v-btn>
    </template>
    <GroupList :items="groups" selectable @selected="onGroupSelected" />
  </UiIndexPage>
  <UiIndexPage v-else :data="{ loading, noData }">
    <template #filters>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Учебный год"
        density="comfortable"
      />
    </template>
    <GroupList :items="clientGroups" />
    <SwampList :items="swamps" @attach="onAttachStart" />
  </UiIndexPage>
</template>

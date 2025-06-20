<script setup lang="ts">
import type { GroupFilters } from '../Group/Filters.vue'

const { clientId } = defineProps<{ clientId: number }>()

const filters = useAvailableYearsFilter()

const groupFilters = ref<GroupFilters>({
  year: currentAcademicYear(),
  program: [],
})

const loading = ref(true)
const selectedSwampId = ref<number>()
const swamps = ref<SwampListResource[]>([])
const clientGroups = ref<GroupListResource[]>([])
const availableYears = ref<Year[]>()
const noData = computed(() => availableYears.value?.length === 0)

const { items: groups } = useIndex<GroupListResource>(
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

async function loadAvailableYears() {
  loading.value = true
  const { data } = await useHttp<Year[]>(
    `contracts`,
    {
      params: {
        client_id: clientId,
        available_years: 1,
      },
    },
  )
  availableYears.value = data.value!

  if (availableYears.value.length > 0) {
    filters.value.year = availableYears.value[0]
  }
  loading.value = false
}

async function loadSwamps() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<SwampListResource>>(
    `swamps`,
    {
      params: {
        ...filters.value,
        client_id: clientId,
        no_group: 1,
      },
    },
  )
  if (data.value) {
    swamps.value = data.value.data
  }
  loading.value = false
}

function onAttachStart(swamp: SwampListResource) {
  selectedSwampId.value = swamp.id
  groupFilters.value = {
    year: filters.value.year!,
    program: [swamp.program],
  }
}

async function loadGroups() {
  const params = {
    ...filters.value,
    client_id: clientId,
  }
  const { data } = await useHttp<ApiResponse<GroupListResource>>(
    `groups`,
    { params },
  )
  if (data.value) {
    clientGroups.value = data.value.data
  }
}

async function onGroupSelected(group: GroupListResource) {
  await useHttp(
    `client-groups`,
    {
      method: 'post',
      params: {
        group_id: group.id,
        contract_version_program_id: selectedSwampId.value!,
      },
    },
  )
  back()
  await loadData()
}

function back() {
  selectedSwampId.value = undefined
}

watch(filters, loadData, { deep: true })

nextTick(loadAvailableYears)
</script>

<template>
  <UiIndexPage v-if="selectedSwampId" :data="{ loading, noData: !groups.length }">
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
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <GroupList :items="clientGroups" />
    <SwampList :items="swamps" @attach="onAttachStart" />
  </UiIndexPage>
</template>

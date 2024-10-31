<script setup lang="ts">
const { clientId } = defineProps<{ clientId: number }>()

const tabName = 'GroupsTab'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}, tabName))

const groupFilters = ref<GroupFilters>({
  year: currentAcademicYear(),
})

const loading = ref(true)
const selectedSwampId = ref<number>()
const swamps = ref<SwampListResource[]>([])
const noData = computed(() => !loading.value && swamps.value.length === 0)

const { items: groups, indexPageData } = useIndex<GroupListResource, GroupFilters>(
  `groups`,
  groupFilters,
  {
    instantLoad: false,
    disableSaveFilters: true,
  },
)

async function loadSwamps() {
  loading.value = true
  const params = {
    ...filters.value,
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
    program: swamp.program,
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
  await loadSwamps()
}

function back() {
  selectedSwampId.value = undefined
}

watch(filters, (newVal) => {
  loadSwamps()
  saveFilters(newVal, tabName)
}, { deep: true })

nextTick(loadSwamps)
</script>

<template>
  <UiIndexPage v-if="selectedSwampId" :data="indexPageData">
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
    <GroupList :items="groups" selectable @select="onGroupSelected" />
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
    <SwampList :items="swamps" @attach="onAttachStart" />
  </UiIndexPage>
</template>

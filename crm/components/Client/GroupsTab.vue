<script setup lang="ts">
import type { GroupFilters } from '../Group/Filters.vue'
import type { SwampListResource } from '../Swamp'
import { mdiArrowRightThin } from '@mdi/js'

const { clientId } = defineProps<{ clientId: number }>()

const filters = useAvailableYearsFilter()
const isGroupControlWindow = ref(false)

const groupFilters = ref<GroupFilters>({
  year: currentAcademicYear(),
  program: [],
})

const loading = ref(true)
const swamps = ref<SwampListResource[]>([])
const availableYears = ref<Year[]>()
const noData = computed(() => availableYears.value?.length === 0)
const teeth = ref<Teeth>()
const groups = ref<GroupListResource[]>()
const noGroups = computed(() => groups.value !== undefined && groups.value.length === 0)

// const { items: groups, indexPageData, reloadData: loadGroups } = useIndex<GroupListResource>(
//   `groups`,
//   groupFilters,
//   {
//     instantLoad: false,
//     saveFilters: false,
//     scrollContainerSelector: false,
//     staticFilters: {
//       tab_client_id: clientId,
//     },
//   },
// )

async function loadGroups() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<GroupListResource>>(
    `groups`,
    {
      params: {
        tab_client_id: clientId,
        ...transformArrayKeys(groupFilters.value),
      },
    },
  )
  groups.value = data.value!.data
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
      },
    },
  )
  if (data.value) {
    swamps.value = data.value.data
  }
  loading.value = false
}

async function loadTeeth() {
  const { data } = await useHttp<Teeth>(`teeth`, {
    params: {
      year: filters.value.year,
      client_id: clientId,
    },
  })
  teeth.value = data.value!
}

async function addToGroup(g: GroupListResource) {
  const { error } = await useHttp(
    `client-groups`,
    {
      method: 'post',
      params: {
        group_id: g.id,
        client_id: clientId,
      },
    },
  )
  if (error.value) {
    useGlobalMessage(error.value.data.message, 'error')
    return
  }
  await loadGroups()
  // itemUpdated('group', g.id, 'instant')
  await loadTeeth()
  await loadSwamps()
}

async function removeFromGroup(g: GroupListResource) {
  await useHttp(`client-groups/${g.swamp!.id}`, { method: 'delete' })
  await loadGroups()
  // itemUpdated('group', g.id, 'instant')
  await loadTeeth()
  await loadSwamps()
}

function goGroupControl() {
  isGroupControlWindow.value = true
  // изменение фильтра вызовет загрузку данных
  groupFilters.value = {
    year: filters.value.year!,
    program: [...new Set(swamps.value.map(s => s.program))],
  }
  loadTeeth()
}

function back() {
  isGroupControlWindow.value = false
}

watch(filters, loadSwamps, { deep: true })
watch(groupFilters, loadGroups, { deep: true })

nextTick(loadAvailableYears)
</script>

<template>
  <UiIndexPage v-if="isGroupControlWindow" :data="{ loading: loading && !groups, noData: noGroups }" sticky>
    <template #filters>
      <ProgramSelector v-model="groupFilters.program" multiple />
      <TeethBar v-if="teeth" :items="teeth" style="width: fit-content" />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="back()">
        <template #prepend>
          <v-icon icon="$back" />
        </template>
        назад
      </v-btn>
    </template>
    <GroupList v-if="groups" :items="groups" :class="{ 'element-loading': loading }">
      <template #default="{ group }">
        <template v-if="group.swamp">
          <td :class="`swamp-status swamp-status--${group.swamp.status}`">
            <div class="pl-3">
              {{ group.swamp.lessons_conducted }}
              <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
              {{ group.swamp.total_lessons }}
            </div>
          </td>
          <td :class="`swamp-status swamp-status--${group.swamp.status}`">
            <span>
              {{ SwampStatusLabel[group.swamp.status] }}
            </span>
            <div class="table-actionss">
              <v-btn color="error" density="comfortable" @click="removeFromGroup(group)">
                <span class="text-white">
                  убрать из группы
                </span>
              </v-btn>
            </div>
          </td>
        </template>
        <template v-else>
          <td colspan="100">
            <div class="pl-3">
              <div v-if="group.is_program_used" class="text-success">
                программа использована
              </div>
              <UiIfSet :value="group.overlap?.count">
                <template #empty>
                  нет пересечений
                </template>
                {{ group.overlap!.count }} пересечений
                ({{ group.overlap!.programs.map(e => ProgramShortLabel[e]).join(', ') }})
              </UiIfSet>
            </div>
            <div class="table-actionss">
              <v-btn color="secondary" density="comfortable" @click="addToGroup(group)">
                добавить в группу
              </v-btn>
            </div>
          </td>
        </template>
      </template>
    </GroupList>
  </UiIndexPage>
  <UiIndexPage v-else :data="{ loading, noData }">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="goGroupControl()">
        управление группами
      </v-btn>
    </template>
    <SwampList :items="swamps" />
  </UiIndexPage>
</template>

<script setup lang="ts">
import type { SwampListResource } from '.'
import type { GroupFilters } from '../Group/Filters.vue'
import { mdiArrowRightThin } from '@mdi/js'

const { clientId, year, swamps } = defineProps<{
  swamps: SwampListResource[]
  clientId: number
  year: Year
}>()

defineEmits<{
  back: []
}>()

const loading = ref(false)
const teeth = ref<Teeth>()
const items = ref<GroupListResource[]>()

// TODO: переделать в диалог
const panelElement = document.documentElement.querySelector('.panel')!

const noData = computed(() => items.value !== undefined && items.value.length === 0)

const filters = ref<GroupFilters>({
  year: currentAcademicYear(),
  program: swamps.map(e => e.program),
})

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<GroupListResource>>(
    `groups`,
    {
      params: {
        tab_client_id: clientId,
        ...transformArrayKeys(filters.value),
      },
    },
  )
  items.value = data.value!.data
  loading.value = false
  await loadTeeth()
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
  await loadData()
}

async function loadTeeth() {
  const { data } = await useHttp<Teeth>(
    `teeth`,
    {
      params: {
        year,
        client_id: clientId,
      },
    },
  )
  teeth.value = data.value!
}

async function removeFromGroup(g: GroupListResource) {
  await useHttp(`client-groups/${g.swamp!.id}`, { method: 'delete' })
  await loadData()
}

onMounted(() => {
  panelElement.style.display = 'none'
})

onUnmounted(() => {
  panelElement.style.display = 'block'
})

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading: loading && !items, noData }" sticky>
    <template #filters>
      <ProgramSelector v-model="filters.program" multiple />
      <TeethBar v-if="teeth" :items="teeth" style="width: fit-content" />
    </template>
    <template #buttons>
      <div class="d-flex align-center ga-4">
        <v-btn icon="$close" :size="48" color="primary" @click="$emit('back')" />
      </div>
    </template>
    <GroupList v-if="items" :items="items" :class="{ 'element-loading': loading }">
      <template #default="{ group }">
        <template v-if="group.swamp">
          <td :class="`swamp-status--${group.swamp.status}`">
            <div class="pl-3">
              <div>
                {{ group.swamp.lessons_conducted }}
                <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
                {{ group.swamp.total_lessons }}
              </div>
              <div>
                {{ SwampStatusLabel[group.swamp.status] }}
              </div>

              <div v-if="group.overlap?.count">
                {{ group.overlap!.count }} пересечений
                ({{ group.overlap!.programs.map(e => ProgramShortLabel[e]).join(', ') }})
              </div>
            </div>
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
              <template v-if="group.overlap?.count">
                {{ group.overlap!.count }} пересечений
                ({{ group.overlap!.programs.map(e => ProgramShortLabel[e]).join(', ') }})
              </template>
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
</template>

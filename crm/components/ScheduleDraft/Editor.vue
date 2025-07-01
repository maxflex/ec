<script setup lang="ts">
import type { SwampEditorData, SwampEditorProgram } from '.'
import type { SwampListResource } from '../Swamp'
import type { ClientResource } from '~/components/Client'
import { mdiArrowRightThin } from '@mdi/js'
import { apiUrl } from '.'

const { client, year, swamps } = defineProps<{
  swamps: SwampListResource[]
  client: ClientResource
  year: Year
}>()

defineEmits<{ back: [] }>()

const loading = ref(false)
const btnLoading = ref(false)
const teeth = ref<Teeth>()
const swampEditorData = ref<SwampEditorData>()

// TODO: переделать в диалог
const panelElement = document.documentElement.querySelector('.panel')!

const programs = ref<SwampEditorProgram[]>(swamps.map(swamp => ({
  id: swamp.id,
  program: swamp.program,
})),
)

const newPrograms = ref<Program[]>([])

const programsWithData = computed(() => {
  if (swampEditorData.value === undefined) {
    return []
  }
  return programs.value.filter(p => p.id in swampEditorData.value!)
})

async function loadData() {
  loading.value = true
  const { data } = await useHttp<SwampEditorData>(
    `${apiUrl}/get-data`,
    {
      method: 'POST',
      body: {
        client_id: client.id,
        year,
        programs: programs.value,
      },
    },
  )
  swampEditorData.value = data.value!
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
        client_id: client.id,
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
        client_id: client.id,
      },
    },
  )
  teeth.value = data.value!
}

async function save() {
  btnLoading.value = true
  await useHttp(
    `${apiUrl}/save`,
    {
      method: 'POST',
      body: {
        year,
        client_id: client.id,
        data: programs.value.map(p => ({
          ...p,
          group_id: swampEditorData.value![p.id].swamp?.group_id || null,
        })),
      },
    },
  )
  btnLoading.value = false
}

async function removeFromGroup(g: GroupListResource) {
  await useHttp(
    `client-groups/${g.swamp!.id}`,
    { method: 'delete' },
  )
  await loadData()
}

async function onNewProgramsSaved(p: Program[]) {
  newPrograms.value = p
  p.every(e => programs.value.push({
    id: newId(),
    program: e,
  }))
  await loadData()
  smoothScroll('main', 'bottom', 'instant')
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
  <UiIndexPage
    class="schedule-project"
    :data="{ loading: loading && !swampEditorData, noData: false }"
    sticky
  >
    <template #filters>
      <!-- <div class="d-flex align-center ga-6">
        <UiAvatar :item="client" :size="60" />
        <div class="panel-info pa-0" style="border: none">
          <div>
            <div>
              ученик
            </div>
            <div>
              {{ formatName(client) }}
            </div>
          </div>
        </div>
      </div> -->
      <div class="schedule-project__header">
        <v-btn icon="$back" :size="44" variant="plain" @click="$emit('back')" />
        {{ formatName(client) }}
      </div>
      <TeethBar v-if="teeth" :items="teeth" style="width: fit-content" />
    </template>
    <template #buttons>
      <div class="d-flex align-center ga-4">
        <v-menu>
          <template #activator="{ props }">
            <v-btn color="primary" v-bind="props" :loading="btnLoading">
              действия
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="save()">
              сохранить проект
            </v-list-item>
            <v-list-item @click="() => contractVersionDialog?.newVersion(selectedContract!)">
              загрузить проект
            </v-list-item>
            <v-list-item @click="() => contractPaymentDialog?.create(selectedContract!)">
              применить проект
            </v-list-item>
          </v-list>
        </v-menu>
        <!-- <v-btn icon="$close" :size="44" color="primary" @click="$emit('back')" /> -->
      </div>
    </template>
    <template v-if="swampEditorData">
      <div v-for="p in programsWithData" :key="p.id" class="schedule-project__data" :class="{ 'element-loading': loading }">
        <div class="schedule-project__contract">
          <div class="schedule-project__contract-header">
            <span>
              <template v-if="swampEditorData[p.id].contract">
                Договор №{{ swampEditorData[p.id].contract.id }}
              </template>
              <span v-else class="text-gray">
                Проект договора
              </span>
            </span>
            <span>
              {{ ProgramLabel[p.program] }}
            </span>
            <template v-if="swampEditorData[p.id].swamp">
              <span :class="`swamp-status swamp-status--${swampEditorData[p.id].swamp.status}`" style="background-color: transparent;">
                <span>
                  {{ SwampStatusLabel[swampEditorData[p.id].swamp.status] }}
                </span>
              </span>
              <span>
                {{ swampEditorData[p.id].swamp.lessons_conducted }}
                <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
                {{ swampEditorData[p.id].swamp.total_lessons }}
              </span>
            </template>
          </div>
        </div>
        <ScheduleDraftList v-if="swampEditorData[p.id].groups.length" :items="swampEditorData[p.id].groups" />
        <div v-else class="schedule-project__no-groups">
          <UiNoData>
            не найдено групп
          </UiNoData>
        </div>
      </div>
      <div class="schedule-project__data" :class="{ 'element-loading': loading }">
        <div class="schedule-project__contract">
          <ProgramSelectorMenu :pre-selected="newPrograms" @saved="onNewProgramsSaved" />
        </div>
      </div>
    </template>
  </UiIndexPage>
</template>

<style lang="scss">
.schedule-project {
  &__header {
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 2px;
    width: fit-content !important;
    padding-right: 20px;
  }

  &__filters {
    --height: 64px !important;
    padding-left: 7px !important;
  }

  &__contract {
    // padding: var(--padding);
    padding: var(--padding);
    // background: rgba(var(--v-theme-bg), 0.1);
    background-color: rgb(var(--v-theme-bg));
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    justify-content: space-between;
    border-top: 1px solid rgb(var(--v-theme-border));
    border-bottom: 1px solid rgb(var(--v-theme-border));
    &-header {
      display: flex;
      align-items: center;
      gap: 20px;

      & > span:first-child {
        font-weight: bold;
      }
    }
    .program-selector {
      max-width: 256px !important;
      .v-input__append {
        display: none;
      }
    }
  }

  &__data {
    &:first-child {
      .schedule-project__contract {
        border-top: none !important;
      }
    }
  }

  &__no-groups {
    height: 200px;
    position: relative;
  }
}
</style>

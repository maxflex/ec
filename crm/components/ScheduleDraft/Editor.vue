<script setup lang="ts">
import type { ScheduleDraftGroup, ScheduleDraftProgram, ScheduleDraftResource } from '.'
import type { SwampListResource } from '../Swamp'
import type { ClientResource } from '~/components/Client'
import { mdiArrowRightThin, mdiChevronRight } from '@mdi/js'
import { apiUrl } from '.'

const { client, year } = defineProps<{
  swamps: SwampListResource[]
  client: ClientResource
  year: Year
}>()

defineEmits<{ back: [] }>()

const loading = ref(false)
const btnLoading = ref(false)
const teeth = ref<Teeth>()
const items = ref<ScheduleDraftProgram[]>()

// ID текущего загруженного / сохранённого проекта. Нужно для удаления
const currentDraftId = ref<number>()

// сохранённые проекты расписания (доступные для загрузки)
const savedDrafts = ref<ScheduleDraftResource[]>([])

// TODO: переделать в диалог
const panelElement = document.documentElement.querySelector('.panel')! as HTMLElement

const newPrograms = ref<Program[]>([])

async function getInitial() {
  loading.value = true
  const { data } = await useHttp<ScheduleDraftProgram[]>(
    `${apiUrl}/get-initial`,
    {
      params: {
        year,
        client_id: client.id,
      },
    },
  )
  items.value = data.value!
  loading.value = false
  smoothScroll('main', 'top', 'instant')
  loadSavedDrafts()
}

async function loadSavedDrafts() {
  const { data } = await useHttp<ApiResponse<ScheduleDraftResource>>(apiUrl, {
    params: {
      year,
      client_id: client.id,
    },
  })
  savedDrafts.value = data.value!.data
}

async function loadSavedDraft(savedDraft: ScheduleDraftResource) {
  loading.value = true
  btnLoading.value = true
  const { data } = await useHttp<ScheduleDraftProgram[]>(
    `${apiUrl}/${savedDraft.id}`,
  )
  items.value = data.value!

  // если в загруженном проекте есть newPrograms
  if (items.value.some(e => e.id <= 0)) {
    newPrograms.value = items.value.filter(e => e.id <= 0).map(e => e.program)
  }

  loading.value = false
  btnLoading.value = false
  currentDraftId.value = savedDraft.id
  smoothScroll('main', 'top', 'instant')
}

async function deleteCurrentDraft() {
  btnLoading.value = true
  await useHttp<ScheduleDraftProgram[]>(
    `${apiUrl}/${currentDraftId.value}`,
    { method: 'DELETE' },
  )
  savedDrafts.value.splice(
    savedDrafts.value.findIndex(e => e.id === currentDraftId.value),
    1,
  )
  btnLoading.value = false
  useGlobalMessage(`<b>ID ${currentDraftId.value}</b> – проект удалён`, 'success')
  currentDraftId.value = undefined
}

async function save() {
  // btnLoading.value = true
  const { data } = await useHttp<ScheduleDraftResource>(
    `${apiUrl}/save`,
    { method: 'POST' },
  )
  currentDraftId.value = data.value!.id
  // btnLoading.value = false
  useGlobalMessage('Проект сохранён', 'success')
  loadSavedDrafts()
}

async function apply() {
  if (!confirm('Применить текущий проект?')) {
    return
  }
  btnLoading.value = true
  const { error } = await useHttp(`${apiUrl}/apply`, { method: 'POST' })
  if (error.value) {
    useGlobalMessage(`<b>Ошибка применения проекта.</b> ${error.value.data.message}`, 'error')
    btnLoading.value = false
    return
  }
  await getInitial()
  btnLoading.value = false
  useGlobalMessage('Проект успешно применён', 'success')
}

async function getTeeth() {
  const { data } = await useHttp<Teeth>(`${apiUrl}/get-teeth`)
  teeth.value = data.value!
}

async function addToGroup(p: ScheduleDraftProgram, g: ScheduleDraftGroup) {
  loading.value = true
  const { data, error } = await useHttp<ScheduleDraftProgram[]>(
    `${apiUrl}/add-to-group`,
    {
      method: 'post',
      body: {
        program_id: p.id,
        group_id: g.id,
      },
    },
  )
  if (error.value) {
    useGlobalMessage(`<b>${ProgramShortLabel[p.program]}</b> – по этой программе ученик находится в другой группе`, 'error')
  }
  else {
    items.value = data.value!
  }
  loading.value = false
}

async function removeFromGroup(g: ScheduleDraftGroup) {
  loading.value = true
  const { data } = await useHttp<ScheduleDraftProgram[]>(
    `${apiUrl}/remove-from-group`,
    {
      method: 'post',
      body: {
        program_id: g.swamp!.id,
      },
    },
  )
  items.value = data.value!
  loading.value = false
}

async function onNewProgramsUpdated(newVal: Program[]) {
  loading.value = true
  newPrograms.value = newVal
  const { data } = await useHttp<ScheduleDraftProgram[]>(
    `${apiUrl}/new-programs`,
    {
      method: 'post',
      body: {
        new_programs: newPrograms.value,
      },
    },
  )
  items.value = data.value!
  loading.value = false
  smoothScroll('main', 'bottom', 'instant')
}

function removeNewProgram(p: ScheduleDraftProgram) {
  newPrograms.value = newPrograms.value.filter(e => e !== p.program)
  onNewProgramsUpdated(newPrograms.value)
}

watch(items, getTeeth)

onMounted(() => {
  panelElement.style.display = 'none'
})

onUnmounted(() => {
  panelElement.style.display = 'block'
})

nextTick(getInitial)
</script>

<template>
  <UiIndexPage
    class="schedule-project"
    :data="{ loading: loading && !items, noData: false }"
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
            <v-list-item @click="apply()">
              применить проект
            </v-list-item>
            <v-list-item @click="save()">
              сохранить проект
            </v-list-item>
            <v-list-item link :disabled="savedDrafts.length === 0">
              загрузить проект
              <template v-if="savedDrafts.length" #append>
                <v-icon size="small" :icon="mdiChevronRight" />
              </template>
              <v-menu activator="parent" submenu>
                <v-list>
                  <v-list-item v-for="savedDraft in savedDrafts" :key="savedDraft.id" @click="loadSavedDraft(savedDraft)">
                    <v-list-item-title>
                      <div>
                        Проект от {{ formatDateTime(savedDraft.created_at) }}
                      </div>
                      <div class="text-caption text-gray">
                        <span class="pr-2">
                          ID {{ savedDraft.id }}
                        </span>
                        {{ formatName(savedDraft.user) }}
                      </div>
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </v-list-item>
            <v-list-item v-if="currentDraftId" class="text-error" @click="deleteCurrentDraft()">
              удалить проект
            </v-list-item>
          </v-list>
        </v-menu>
        <!-- <v-btn icon="$close" :size="44" color="primary" @click="$emit('back')" /> -->
      </div>
    </template>
    <template v-if="items">
      <div v-for="item in items" :key="item.id" class="schedule-project__data" :class="{ 'element-loading': loading }">
        <div class="schedule-project__contract">
          <div class="schedule-project__contract-header">
            <span>
              <template v-if="item.contract">
                Договор №{{ item.contract.id }}
              </template>
              <span v-else class="text-gray">
                Проект договора
              </span>
            </span>
            <span>
              {{ ProgramLabel[item.program] }}
            </span>
            <template v-if="item.swamp">
              <span v-if="item.contract">
                {{ item.swamp.lessons_conducted }}
                <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
                {{ item.swamp.total_lessons }}
              </span>
              <span :class="`swamp-status swamp-status--${item.swamp.status}`" style="background-color: transparent;">
                <span>
                  {{ SwampStatusLabel[item.swamp.status] }}
                </span>
              </span>
            </template>
            <v-btn
              v-if="!item.contract"
              :size="30"
              icon="$plus"
              density="compact"
              class="schedule-project__remove"
              variant="plain"
              color="error"
              @click="removeNewProgram(item)"
            />
          </div>
        </div>
        <ScheduleDraftList
          v-if="item.groups.length"
          :items="item.groups"
          @add-to-group="g => addToGroup(item, g)"
          @remove-from-group="removeFromGroup"
        />
        <div v-else class="schedule-project__no-groups">
          <UiNoData>
            не найдено групп
          </UiNoData>
        </div>
      </div>
      <div class="schedule-project__data" :class="{ 'element-loading': loading }">
        <div class="schedule-project__contract">
          <ProgramSelectorMenu
            :pre-selected="newPrograms"
            include-pre-selected
            @saved="onNewProgramsUpdated"
          />
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
      cursor: default;

      &:hover {
        .schedule-project__remove {
          opacity: 0.8;
        }
      }

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

  &__remove {
    transform: rotate(45deg);
    transition: opacity ease-in-out 0.2s;
    opacity: 0;
    left: -10px;
    &:hover {
      opacity: 1 !important;
    }
  }
}
</style>

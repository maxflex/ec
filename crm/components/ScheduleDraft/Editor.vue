<script setup lang="ts">
import type { ScheduleDraftApplySummaryDialog } from '#components'
import type { SavedScheduleDraft, ScheduleDraft, ScheduleDraftGroup, ScheduleDraftProgram } from '.'
import type { ClientResource } from '~/components/Client'
import { mdiArrowRightThin, mdiChevronRight } from '@mdi/js'
import { apiUrl } from '.'

const { client, year } = defineProps<{
  client: ClientResource
  year: Year
}>()

defineEmits<{ back: [] }>()

const applyDialog = ref<InstanceType<typeof ScheduleDraftApplySummaryDialog>>()
const loading = ref(false)
const btnLoading = ref(false)
const teeth = ref<Teeth>()
const scheduleDraft = ref<ScheduleDraft>()
const selectedContractId = ref<number>() // ID выбранной вкладки договора

// ID текущего загруженного / сохранённого проекта. Нужно для удаления
const currentDraftId = ref<number>()

// сохранённые проекты расписания (доступные для загрузки)
const savedDrafts = ref<SavedScheduleDraft[]>([])

async function fromActualContracts() {
  loading.value = true
  const { data } = await useHttp<ScheduleDraft>(
    `${apiUrl}/from-actual-contracts`,
    {
      params: {
        year,
        client_id: client.id,
      },
    },
  )
  scheduleDraft.value = data.value!
  selectedContractId.value = Number.parseInt(Object.keys(scheduleDraft.value)[0])
  loading.value = false
  smoothScroll('main', 'top', 'instant')
  loadSavedDrafts()
}

async function loadSavedDrafts() {
  const { data } = await useHttp<ApiResponse<SavedScheduleDraft>>(apiUrl, {
    params: {
      client_id: client.id,
    },
  })
  savedDrafts.value = data.value!.data
}

async function loadSavedDraft(savedDraft: SavedScheduleDraft) {
  loading.value = true
  btnLoading.value = true
  const { data } = await useHttp<ScheduleDraft>(
    `${apiUrl}/${savedDraft.id}`,
  )
  scheduleDraft.value = data.value!
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
  const { data } = await useHttp<SavedScheduleDraft>(
    `${apiUrl}/save`,
    { method: 'POST' },
  )
  currentDraftId.value = data.value!.id
  // btnLoading.value = false
  useGlobalMessage('Проект сохранён', 'success')
  loadSavedDrafts()
}

async function apply() {
  applyDialog.value?.open(scheduleDraft.value)
  // btnLoading.value = true
  // const { error } = await useHttp(`${apiUrl}/apply`, { method: 'POST' })
  // if (error.value) {
  //   useGlobalMessage(`<b>Ошибка применения проекта.</b> ${error.value.data.message}`, 'error')
  //   btnLoading.value = false
  //   return
  // }
  // await fromActualContracts()
  // btnLoading.value = false
  // useGlobalMessage('Проект успешно применён', 'success')
}

async function getTeeth() {
  const { data } = await useHttp<Teeth>(`${apiUrl}/get-teeth`)
  teeth.value = data.value!
}

async function addToGroup(p: ScheduleDraftProgram, g: ScheduleDraftGroup) {
  loading.value = true
  const { data, error } = await useHttp<ScheduleDraft>(
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
    return
  }

  scheduleDraft.value = data.value!
  loading.value = false
}

async function removeFromGroup(g: ScheduleDraftGroup) {
  loading.value = true
  const { data } = await useHttp<ScheduleDraft>(
    `${apiUrl}/remove-from-group`,
    {
      method: 'post',
      body: {
        program_id: g.swamp!.id,
      },
    },
  )
  scheduleDraft.value = data.value!
  loading.value = false
}

async function addPrograms(newPrograms: Program[]) {
  loading.value = true
  const { data } = await useHttp<ScheduleDraft>(
    `${apiUrl}/add-programs`,
    {
      method: 'post',
      body: {
        new_programs: newPrograms,
      },
    },
  )
  scheduleDraft.value = data.value!
  loading.value = false
}

async function removeProgram(p: ScheduleDraftProgram) {
  loading.value = true
  const { data } = await useHttp<ScheduleDraft>(
    `${apiUrl}/remove-program`,
    {
      method: 'post',
      body: {
        id: p.id,
      },
    },
  )
  scheduleDraft.value = data.value!
  loading.value = false
}

watch(scheduleDraft, getTeeth)
watch(selectedContractId, () => smoothScroll('main', 'top', 'instant'))

nextTick(fromActualContracts)
</script>

<template>
  <UiIndexPage
    class="schedule-draft"
    :data="{ loading: loading && !scheduleDraft, noData: false }"
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
      <div class="schedule-draft__header">
        <v-btn icon="$back" :size="44" variant="plain" color="black" :to="{ name: 'clients-id', params: { id: client.id } }" />
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
    <template v-if="scheduleDraft && selectedContractId">
      <div class="tabs">
        <div
          v-for="(_, contractId) in scheduleDraft"
          :key="contractId"
          class="tabs-item"
          :class="{ 'tabs-item--active': selectedContractId === Number.parseInt(contractId) }"
          @click="selectedContractId = Number.parseInt(contractId as string)"
        >
          <template v-if="contractId > 0">
            договор №{{ contractId }}
          </template>
          <template v-else>
            новый договор
          </template>
        </div>
      </div>
      <div v-for="p in scheduleDraft[selectedContractId]" :key="p.id" class="schedule-draft__programs">
        <div class="schedule-draft__program-info">
          <span>
            {{ ProgramLabel[p.program] }}
          </span>
          <ScheduleDraftIcon v-if="p.id < 0">
            добавлено в черновике
          </ScheduleDraftIcon>
          <span v-else>
            {{ p.swamp.lessons_conducted }}
            <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
            {{ p.swamp.total_lessons }}
          </span>
          <span :class="`swamp-status swamp-status--${p.swamp.status}`" style="background-color: transparent;">
            <span>
              {{ SwampStatusLabel[p.swamp.status] }}
            </span>
          </span>
          <v-btn
            v-if="p.id < 0"
            :size="30"
            icon="$plus"
            density="compact"
            class="schedule-draft__remove"
            variant="plain"
            @click="removeProgram(p)"
          />
        </div>
        <ScheduleDraftGroupList
          v-if="p.groups.length"
          :items="p.groups"
          :contract-id="selectedContractId"
          @add-to-group="g => addToGroup(p, g)"
          @remove-from-group="removeFromGroup"
        />
        <div v-else class="schedule-draft__no-groups">
          <UiNoData>
            не найдено групп
          </UiNoData>
        </div>
      </div>
      <div class="container">
        <ProgramSelectorMenu
          :pre-selected="scheduleDraft[selectedContractId].map(e => e.program)"
          @saved="addPrograms"
        >
          <template #activator="{ props }">
            <v-btn v-bind="props" color="primary">
              добавить программы
              <v-icon icon="$expand" class="ml-1" />
            </v-btn>
          </template>
        </ProgramSelectorMenu>
      </div>
    </template>
  </UiIndexPage>
  <ScheduleDraftApplySummaryDialog ref="applyDialog" />
</template>

<style lang="scss">
.schedule-draft {
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

  &__program-info {
    padding: var(--padding);
    display: flex;
    align-items: center;
    gap: 20px;
    cursor: default;
    background-color: rgb(var(--v-theme-bg));
    border-bottom: 1px solid rgb(var(--v-theme-border));

    &:hover {
      .schedule-draft__remove {
        opacity: 0.8;
      }
    }

    & > span:first-child {
      font-weight: bold;
    }
  }

  &__contract {
    background-color: rgb(var(--v-theme-bg));
    padding: var(--padding);
    padding-bottom: 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  &__no-groups {
    height: 200px;
    position: relative;
    border-bottom: 1px solid rgb(var(--v-theme-border));
  }

  &__remove {
    transform: rotate(45deg);
    transition: opacity ease-in-out 0.2s;
    opacity: 0;
    left: -10px;
    color: rgb(var(--v-theme-gray));
    &:hover {
      opacity: 1 !important;
      color: rgb(var(--v-theme-error));
    }
  }

  .tabs {
    position: sticky;
    top: 64px;
    z-index: 1;
    background: white;
  }
}
</style>

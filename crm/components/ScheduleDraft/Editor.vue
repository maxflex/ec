<script setup lang="ts">
import type { SavedScheduleDraftResource, ScheduleDraft, ScheduleDraftGroup, ScheduleDraftProgram } from '.'
import { ContractVersionDialog } from '#components'
import { mdiArrowRightThin, mdiChevronRight } from '@mdi/js'
import { apiUrl, isGroupChangedInContract } from '.'

const { client, year, savedDraft } = defineProps<{
  /**
   * Если загружаем конкретный ID проекта
   */
  savedDraft?: SavedScheduleDraftResource
  client: PersonResource
  year: Year
}>()

defineEmits<{ back: [] }>()

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const btnLoading = ref(false)
const btnCreateLoading = ref(false)
const teeth = ref<Teeth>()
const scheduleDraft = ref<ScheduleDraft>()
const selectedContractId = ref<number>() // ID выбранной вкладки договора
const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()

// сохранённые проекты расписания (доступные для загрузки)
const savedDrafts = ref<SavedScheduleDraftResource[]>([])

async function fromActualContracts() {
  loading.value = true
  const { data } = await useHttp<ScheduleDraft>(
    `${apiUrl}/from-actual-contracts`,
    {
      params: {
        year,
        id: savedDraft?.id,
        client_id: client.id,
      },
    },
  )
  scheduleDraft.value = data.value!
  // выбираем первую вкладку
  selectedContractId.value = Number.parseInt(Object.keys(scheduleDraft.value)[0])
  loading.value = false
  smoothScroll('main', 'top', 'instant')
  loadSavedDrafts()

  if (savedDraft) {
    selectedContractId.value = savedDraft.contract_id || -1
  }
}

async function loadSavedDrafts() {
  const { data } = await useHttp<ApiResponse<SavedScheduleDraftResource>>(apiUrl, {
    params: {
      client_id: client.id,
    },
  })
  savedDrafts.value = data.value!.data
}

async function save() {
  btnLoading.value = true
  const { data } = await useHttp<SavedScheduleDraftResource>(
    `${apiUrl}/save`,
    {
      method: 'POST',
      body: {
        contract_id: selectedContractId.value,
      },
    },
  )
  const id = data.value!.id
  const link = router.resolve({ name: 'schedule-drafts-editor', query: { id } }).href
  useGlobalMessage(`<a href="${link}">Проект №${id}</a> сохранён`, 'success')
  btnLoading.value = false
}

async function applyMoveGroups() {
  loading.value = true
  const { data, error } = await useHttp<ScheduleDraft>(
    `${apiUrl}/apply-move-groups`,
    { method: 'POST' },
  )

  if (error.value) {
    useGlobalMessage(error.value.data?.message, 'error')
    loading.value = false

    return
  }

  scheduleDraft.value = data.value!
  loading.value = false
  useGlobalMessage('Изменения в группах применены', 'success')
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
    loading.value = false

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
        contract_id: selectedContractId.value,
        new_programs: newPrograms,
      },
    },
  )
  scheduleDraft.value = data.value!
  loading.value = false
  smoothScroll('main', 'bottom', 'instant')
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

function jumpToContract(item: ScheduleDraftGroup) {
  selectedContractId.value = item.swamp!.contract_id as number
  nextTick(() => {
    const selector = `#schedule-draft-group-${item.id}${selectedContractId.value ? `-${selectedContractId.value}` : ''}`
    highlight(selector, 'item-updated', 'instant')
  })
}

function getChangesCnt(contractId: number) {
  let cnt = 0

  for (const program of scheduleDraft.value![contractId]) {
    if (program.id < 0) {
      cnt++
    }

    for (const group of program.groups) {
      if (isGroupChangedInContract(group, contractId)) {
        cnt++
      }
    }
  }

  return cnt
}

async function createContract() {
  btnCreateLoading.value = true
  const { data } = await useHttp<{
    contractVersion: ContractVersionResource
    scheduleDraft: SavedScheduleDraftResource
  }>(
    `${apiUrl}/create-contract`,
    {
      method: 'POST',
      body: {
        contract_id: selectedContractId.value,
      },
    },
  )
  contractVersionDialog.value?.createFromDraft(data.value!.contractVersion, data.value!.scheduleDraft)
  btnCreateLoading.value = false
}

async function goToPage(d: SavedScheduleDraftResource) {
  await router.push({ name: 'schedule-drafts-editor', query: { id: d.id } })
  location.reload()
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

    <template v-if="scheduleDraft && selectedContractId">
      <div class="tabs">
        <div
          v-for="(_, contractId) in scheduleDraft"
          :key="contractId"
          class="tabs-item"
          :class="{ 'tabs-item--active': selectedContractId === Number.parseInt(contractId) }"
          @click="selectedContractId = Number.parseInt(contractId)"
        >
          <span v-if="contractId > 0">
            договор №{{ contractId }}
          </span>
          <span v-else>
            новый договор
          </span>
          <v-badge
            v-if="getChangesCnt(Number.parseInt(contractId))"
            color="orange-lighten-3"
            inline
            :content="getChangesCnt(Number.parseInt(contractId))"
          ></v-badge>
        </div>
      </div>
      <div
        v-for="p in scheduleDraft[selectedContractId]"
        :key="p.id"
        class="schedule-draft__programs"
        :class="{ 'element-loading': loading }"
      >
        <div class="schedule-draft__program-info">
          <span>
            {{ ProgramLabel[p.program] }}
          </span>
          <v-chip v-if="p.id < 0" label density="comfortable" color="orange">
            добавлено в черновике
          </v-chip>
          <span v-else>
            {{ p.swamp.lessons_conducted }}
            <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
            {{ p.swamp.total_lessons }}
          </span>
          <span style="background-color: transparent;">
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
          @jump-to-contract="jumpToContract"
        />
        <div v-else class="schedule-draft__no-groups">
          <UiNoData>
            не найдено групп
          </UiNoData>
        </div>
      </div>
      <div class="container d-flex ga-4" :class="{ 'element-loading': loading }">
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
        <v-menu>
          <template #activator="{ props }">
            <v-btn v-bind="props" :loading="btnLoading" :width="272" variant="outlined">
              действия
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="createContract()">
              создать версию на основе проекта
            </v-list-item>
            <v-list-item @click="applyMoveGroups()">
              отконфигурировать группы
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
                  <v-list-item
                    v-for="d in savedDrafts"
                    :key="d.id"
                    :active="savedDraft?.id === d.id"
                    @click="goToPage(d)"
                  >
                    <v-list-item-title>
                      <div>
                        Проект №{{ d.id }}
                      </div>
                      <div class="text-caption text-gray">
                        {{ formatName(d.user) }}
                        {{ formatDateTime(d.created_at) }}
                      </div>
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </v-list-item>
          </v-list>
        </v-menu>
      </div>
    </template>
  </UiIndexPage>
  <ContractVersionDialog ref="contractVersionDialog" />
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

  &__changes-cnt {
    color: rgb(var(--v-theme-gray));
  }

  .tabs {
    position: sticky;
    top: 64px;
    z-index: 1;
    background: white;
    .tabs-item {
      display: flex;
      align-items: center;
      gap: 8px;
    }
  }
}
</style>

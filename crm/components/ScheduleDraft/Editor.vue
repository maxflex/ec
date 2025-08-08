<script setup lang="ts">
import type { SavedScheduleDraftResource, ScheduleDraft, ScheduleDraftGroup, ScheduleDraftProgram } from '.'
import { ContractVersionDialog } from '#components'
import { mdiChevronRight } from '@mdi/js'
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

const router = useRouter()
const loading = ref(false)
const btnLoading = ref(false)
const teeth = ref<Teeth>()
const scheduleDraft = ref<ScheduleDraft>()
const selectedContractId = ref<number>() // ID выбранной вкладки договора
const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
const key = ref(0)

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

async function save(contractId: number) {
  btnLoading.value = true
  const { data } = await useHttp<SavedScheduleDraftResource>(
    `${apiUrl}/save`,
    {
      method: 'POST',
      body: {
        contract_id: contractId,
      },
    },
  )
  const id = data.value!.id
  const link = router.resolve({ name: 'schedule-drafts-editor', query: { id } }).href
  useGlobalMessage(`<a href="${link}">Проект №${id}</a> сохранён`, 'success')
  loadSavedDrafts()
  btnLoading.value = false
}

async function applyMoveGroups(contractId: number) {
  loading.value = true
  const { data, error } = await useHttp<ScheduleDraft>(
    `${apiUrl}/apply-move-groups`,
    {
      method: 'POST',
      body: {
        contract_id: contractId,
      },
    },
  )

  if (error.value) {
    useGlobalMessage(error.value.data?.message, 'error')
    loading.value = false

    return
  }

  scheduleDraft.value = data.value!
  loading.value = false
  useGlobalMessage('Перемещения в группах применены', 'success')
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

async function addPrograms(newPrograms: Program[], contractId: number) {
  selectedContractId.value = Number.parseInt(contractId)
  key.value++
  loading.value = true
  const { data } = await useHttp<ScheduleDraft>(
    `${apiUrl}/add-programs`,
    {
      method: 'post',
      body: {
        contract_id: contractId,
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

function getChangesCnt(contractId: number | string) {
  const cId = Number.parseInt(contractId as string)
  let cnt = 0

  for (const program of scheduleDraft.value![cId]) {
    if (program.id < 0) {
      cnt++
    }

    for (const group of program.groups) {
      if (isGroupChangedInContract(group, cId)) {
        cnt++
      }
    }
  }

  return cnt
}

const savedDraftsByContract = computed<Record<number, SavedScheduleDraftResource[]>>(() => {
  const result: Record<number, SavedScheduleDraftResource[]> = {}
  for (const contractId in scheduleDraft.value) {
    const cId = Number.parseInt(contractId)
    result[cId] = savedDrafts.value.filter(e => e.contract_id === (cId === -1 ? null : cId))
  }
  return result
})

async function loadDraft(d: SavedScheduleDraftResource) {
  selectedContractId.value = d.contract_id || -1
  loading.value = true
  const { data } = await useHttp<ScheduleDraft>(
    `${apiUrl}/load/${d.id}`,
    {
      method: 'POST',
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
        <v-btn
          icon="$back" :size="44" variant="plain" color="black"
          :to="{ name: 'clients-id', params: { id: client.id }, hash: '#contracts' }"
        />
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
          :class="{
            'tabs-item--active': selectedContractId === Number.parseInt(contractId),
          }"
          @click="selectedContractId = Number.parseInt(contractId)"
        >
          <span v-if="contractId > 0">
            договор №{{ contractId }}
          </span>
          <span v-else>
            новый договор
          </span>
          <v-badge
            v-if="getChangesCnt(contractId)"
            color="orange-lighten-3"
            inline
            :content="getChangesCnt(contractId)"
          ></v-badge>
          <v-menu :key="key">
            <template #activator="{ props }">
              <v-icon icon="$next" v-bind="props" />
            </template>
            <v-list>
              <v-list-item link>
                добавить программы
                <template #append>
                  <v-icon :icon="mdiChevronRight" />
                </template>
                <ProgramSelectorNested
                  :pre-selected="scheduleDraft[contractId].map(e => e.program)"
                  @saved="newPrograms => addPrograms(newPrograms, contractId)"
                />
              </v-list-item>
              <v-list-item :disabled="!getChangesCnt(contractId)" @click="contractVersionDialog?.fromDraft({ contractId })">
                {{ contractId < 0 ? 'создать новый договор' : 'добавить версию' }}
                на основе проекта
              </v-list-item>
              <v-list-item :disabled="contractId < 0 || !getChangesCnt(contractId)" @click="applyMoveGroups(contractId)">
                применить перемещения в группах
              </v-list-item>
              <v-list-item :disabled="!getChangesCnt(contractId)" @click="save(contractId)">
                сохранить проект
              </v-list-item>
              <v-list-item
                v-for="d in savedDraftsByContract[contractId]"
                :key="d.id"
                :disabled="d.is_archived"
                @click="loadDraft(d)"
              >
                <div>
                  загрузить проект №{{ d.id }}
                  <v-badge
                    v-if="d.changes > 0"
                    color="orange-lighten-3"
                    inline
                    :content="d.changes"
                  ></v-badge>
                </div>
                <div class="text-caption text-gray">
                  Создал {{ formatName(d.user) }} {{ formatDateTime(d.created_at) }}
                </div>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
      </div>
      <UiNoData v-if="scheduleDraft[selectedContractId].length === 0" />
      <div
        v-for="p in scheduleDraft[selectedContractId]"
        v-else
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
          <ContractVersionProgramStatus :item="p.swamp!" />
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

      .v-icon {
        $size: 20px;
        height: $size;
        width: $size;
        font-size: 14px;
        transform: rotate(90deg);
        transition: transform ease-in-out 0.2s;
        &[aria-expanded='true'] {
          transform: rotate(-90deg);
        }
      }
    }

    .v-badge__wrapper {
      margin: 0 !important;
    }
  }
}
</style>
